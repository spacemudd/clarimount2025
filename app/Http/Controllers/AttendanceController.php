<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceImportRequest;
use App\Models\AttendanceImport;
use App\Models\BayzatSyncBatch;
use App\Services\AttendanceImportService;
use App\Jobs\ProcessBayzatSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceImportService $importService
    ) {}

    public function index(Request $request): Response
    {
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

        return Inertia::render('Attendance/Index', [
            'imports' => $imports,
            'syncStats' => $syncStats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Attendance/Import');
    }

    public function store(AttendanceImportRequest $request)
    {
        try {
            $import = $this->importService->processImport(
                $request->file('file'),
                Auth::id(),
                Auth::user()->team_id
            );

            return redirect()
                ->route('attendance.show', $import)
                ->with('success', __('messages.attendance_import_started'));

        } catch (\Exception $e) {
            return back()
                ->withErrors(['file' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(AttendanceImport $attendance): Response
    {

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
            'import' => $attendance,
            'syncProgress' => $syncProgress,
            'validationSummary' => $validationSummary,
        ]);
    }

    public function retrySync(AttendanceImport $attendance)
    {

        try {
            $this->importService->retryFailedRecords($attendance);

            return back()->with('success', __('messages.sync_retry_initiated'));

        } catch (\Exception $e) {
            return back()->withErrors(['sync' => $e->getMessage()]);
        }
    }

    public function retrySyncBatch(BayzatSyncBatch $batch)
    {

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
