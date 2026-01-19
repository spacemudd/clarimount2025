<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceImportRequest;
use App\Models\AttendanceImport;
use App\Models\BayzatSyncBatch;
use App\Models\Company;
use App\Models\ZkDailyAttendance;
use App\Services\AttendanceImportService;
use App\Jobs\ProcessBayzatSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceImportService $importService
    ) {}

    public function index(Request $request, Company $company): Response
    {
        $user = Auth::user();
        
        // Verify user owns this company
        if (!$user->ownedCompanies()->where('id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }
        
        $ownedCompanyIds = collect([$company->id]);

        $imports = AttendanceImport::query()
            ->with(['user', 'syncBatches.company'])
            ->withCount(['records', 'validRecords', 'invalidRecords'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get sync statistics
        $syncStats = [
            'total_imports' => AttendanceImport::count(),
            'pending_syncs' => BayzatSyncBatch::where('status', 'pending')->count(),
            'failed_syncs' => BayzatSyncBatch::where('status', 'failed')->count(),
        ];

        // Get fingerprint attendance data
        $selectedDate = $request->query('date', Carbon::today('Asia/Riyadh')->format('Y-m-d'));
        $date = Carbon::parse($selectedDate)->format('Y-m-d');
        $search = $request->query('search', '');

        $query = ZkDailyAttendance::query()
            ->select([
                'zk_daily_attendance.*',
                'employees.id as employee_id',
                'employees.first_name',
                'employees.last_name',
                'employees.employee_id as emp_code',
                'employees.company_id',
                'zk_devices.name as device_name',
                'zk_devices.serial_number',
            ])
            ->leftJoin('employees', function ($join) {
                $join->on('employees.fingerprint_device_id', '=', 'zk_daily_attendance.device_pin');
            })
            ->leftJoin('zk_devices', 'zk_devices.id', '=', 'zk_daily_attendance.device_id')
            ->where('zk_daily_attendance.att_date', $date)
            ->where('employees.company_id', $company->id);

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.first_name', 'like', "%{$search}%")
                  ->orWhere('employees.last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(employees.first_name, ' ', employees.last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('employees.employee_id', 'like', "%{$search}%")
                  ->orWhere('zk_daily_attendance.device_pin', 'like', "%{$search}%");
            });
        }

        $fingerprintAttendance = $query
            ->orderBy('zk_daily_attendance.att_date', 'desc')
            ->orderBy('employees.first_name', 'asc')
            ->orderBy('employees.last_name', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Get statistics for selected date (filtered by company)
        $statsQuery = ZkDailyAttendance::query()
            ->leftJoin('employees', function ($join) {
                $join->on('employees.fingerprint_device_id', '=', 'zk_daily_attendance.device_pin');
            })
            ->where('zk_daily_attendance.att_date', $date)
            ->where('employees.company_id', $company->id);

        $fingerprintStats = [
            'present_count' => (clone $statsQuery)->distinct('zk_daily_attendance.device_pin')->count('zk_daily_attendance.device_pin'),
            'total_punches' => (clone $statsQuery)->sum('zk_daily_attendance.punch_count'),
        ];

        return Inertia::render('Attendance/Index', [
            'company' => $company,
            'imports' => $imports,
            'syncStats' => $syncStats,
            'fingerprintAttendance' => $fingerprintAttendance,
            'selectedDate' => $selectedDate,
            'fingerprintStats' => $fingerprintStats,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Attendance/Import');
    }

    public function store(AttendanceImportRequest $request, Company $company)
    {
        $user = Auth::user();
        
        // Verify user owns this company
        if (!$user->ownedCompanies()->where('id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }
        
        try {
            $import = $this->importService->processImport(
                $request->file('file'),
                Auth::id(),
                Auth::user()->team_id
            );

            return redirect()
                ->route('attendance.show', [$company, $import])
                ->with('success', __('messages.attendance_import_started'));

        } catch (\Exception $e) {
            return back()
                ->withErrors(['file' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Company $company, AttendanceImport $attendance): Response
    {
        $user = Auth::user();
        
        // Verify user owns this company
        if (!$user->ownedCompanies()->where('id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        $attendance->load([
            'user',
            'team',
            'records' => function ($query) {
                $query->orderBy('date', 'desc')->orderBy('csv_employee_id');
            },
            'syncBatches.company'
        ]);

        // Get sync progress per company
        $syncProgress = $attendance->syncBatches->map(function ($batch) {
            return [
                'company_id' => $batch->company_id,
                'company_name' => $batch->company->name,
                'status' => $batch->status,
                'total_records' => $batch->total_records,
                'synced_records' => $batch->synced_records,
                'failed_records' => $batch->failed_records,
                'success_rate' => $batch->success_rate,
                'completion_percentage' => $batch->completion_percentage,
                'started_at' => $batch->started_at,
                'completed_at' => $batch->completed_at,
                'error_message' => $batch->error_message,
            ];
        });

        // Get validation errors summary
        $validationSummary = [
            'total_errors' => count($attendance->validation_errors ?? []),
            'unmapped_departments' => $attendance->unmapped_departments ?? [],
            'error_types' => $this->categorizeValidationErrors($attendance->validation_errors ?? []),
        ];

        return Inertia::render('Attendance/Show', [
            'company' => $company,
            'import' => $attendance,
            'syncProgress' => $syncProgress,
            'validationSummary' => $validationSummary,
        ]);
    }

    public function retrySync(Company $company, AttendanceImport $attendance)
    {
        $user = Auth::user();
        
        // Verify user owns this company
        if (!$user->ownedCompanies()->where('id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        try {
            $this->importService->retryFailedRecords($attendance);

            return back()->with('success', __('messages.sync_retry_initiated'));

        } catch (\Exception $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }

    public function retrySyncBatch(Company $company, BayzatSyncBatch $batch)
    {
        $user = Auth::user();
        
        // Verify user owns this company
        if (!$user->ownedCompanies()->where('id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        try {
            // Reset batch status
            $batch->update([
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'error_message' => null,
                'synced_records' => 0,
                'failed_records' => 0,
            ]);

            // Reset associated records
            $batch->attendanceImport->records()
                ->where('company_id', $batch->company_id)
                ->where('bayzat_sync_status', 'failed')
                ->update([
                    'bayzat_sync_status' => 'pending',
                    'bayzat_sync_error' => null,
                ]);

            // Dispatch new sync job
            ProcessBayzatSync::dispatch($batch);

            return back()->with('success', __('messages.batch_sync_retry_initiated'));

        } catch (\Exception $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Employee ID',
            'First Name',
            'Department',
            'Date',
            'Weekday',
            'Check In',
            'Check Out',
            'Clock In',
            'Clock Out',
            'Work Duration',
            'Break Duration',
            'Overtime Duration',
        ];

        $sampleData = [
            ['EMP001', 'John Doe', 'IT Department', '2024-01-15', 'Monday', '09:00:00', '17:00:00', '09:00:00', '17:00:00', '8.0', '1.0', '0.0'],
            ['EMP002', 'Jane Smith', 'HR Department', '2024-01-15', 'Monday', '08:30:00', '16:30:00', '08:30:00', '16:30:00', '8.0', '1.0', '0.0'],
        ];

        $filename = 'attendance_import_template.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, $headers);
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

    private function categorizeValidationErrors(array $errors): array
    {
        $categories = [
            'employee_not_found' => 0,
            'department_not_mapped' => 0,
            'invalid_date' => 0,
            'missing_fields' => 0,
            'other' => 0,
        ];

        foreach ($errors as $error) {
            if (str_contains($error, 'Employee ID') && str_contains($error, 'not found')) {
                $categories['employee_not_found']++;
            } elseif (str_contains($error, 'Department') && str_contains($error, 'not mapped')) {
                $categories['department_not_mapped']++;
            } elseif (str_contains($error, 'date format')) {
                $categories['invalid_date']++;
            } elseif (str_contains($error, 'required')) {
                $categories['missing_fields']++;
            } else {
                $categories['other']++;
            }
        }

        return $categories;
    }
}
