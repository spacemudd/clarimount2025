<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AttendanceImport;
use App\Models\AttendanceImportRecord;
use App\Models\BayzatSyncBatch;
use App\Models\Company;
use App\Models\Employee;
use App\Jobs\ProcessBayzatSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceImportService
{
    private const REQUIRED_HEADERS = [
        'Employee ID',
        'First Name',
        'Department',
        'Date',
        'Weekday',
        'Check In',
        'Check Out',
    ];

    public function processImport(UploadedFile $file, int $userId, ?int $teamId): AttendanceImport
    {
        return DB::transaction(function () use ($file, $userId, $teamId) {
            // Store the file
            $filename = $file->getClientOriginalName();
            $filePath = $file->store('attendance_imports', 'local');

            // Create import record
            $import = AttendanceImport::create([
                'filename' => $filename,
                'file_path' => $filePath,
                'user_id' => $userId,
                'team_id' => $teamId,
            ]);

            try {
                $import->markAsStarted();

                // Process the CSV file
                $this->processCsvFile($import, $filePath);

                // Queue valid records for Bayzat sync
                $this->queueForBayzatSync($import);

                $import->markAsCompleted();
            } catch (\Exception $e) {
                Log::error('Attendance import failed', [
                    'import_id' => $import->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $import->markAsFailed($e->getMessage());
                throw $e;
            }

            return $import;
        });
    }

    private function processCsvFile(AttendanceImport $import, string $filePath): void
    {
        $fullPath = Storage::disk('local')->path($filePath);
        $handle = fopen($fullPath, 'r');

        if (!$handle) {
            throw new \RuntimeException('Unable to open CSV file');
        }

        // Read and validate headers (handle title rows)
        $headers = $this->findValidHeaders($handle);
        $this->validateHeaders($headers);

        // Get existing employees and company mappings for validation
        $existingEmployeeIds = Employee::pluck('employee_id')->toArray();
        $companyMappings = Company::whereNotNull('fingerprint_report_name')
            ->pluck('id', 'fingerprint_report_name')
            ->toArray();

        $totalRecords = 0;
        $validRecords = 0;
        $invalidRecords = 0;
        $unmappedDepartments = [];
        $validationErrors = [];

        // Process each row
        while (($row = fgetcsv($handle)) !== false) {
            $totalRecords++;
            
            try {
                $record = $this->parseRow($headers, $row);
                $validation = $this->validateRecord($record, $existingEmployeeIds, $companyMappings);

                // Create import record
                AttendanceImportRecord::create([
                    'attendance_import_id' => $import->id,
                    'employee_id' => $validation['employee_id'],
                    'company_id' => $validation['company_id'],
                    'csv_employee_id' => $record['Employee ID'],
                    'first_name' => $record['First Name'],
                    'fingerprint_department' => $record['Department'],
                    'date' => Carbon::createFromFormat('Y-m-d', $record['Date']),
                    'weekday' => $record['Weekday'],
                    'check_in' => $this->parseTime($record['Check In']),
                    'check_out' => $this->parseTime($record['Check Out']),
                    'clock_in' => $this->parseTime($record['Clock In'] ?? null),
                    'clock_out' => $this->parseTime($record['Clock Out'] ?? null),
                    'work_duration' => $this->parseDuration($record['Work Duration'] ?? null),
                    'break_duration' => $this->parseDuration($record['Break Duration'] ?? null),
                    'overtime_duration' => $this->parseDuration($record['Overtime Duration'] ?? null),
                    'validation_errors' => $validation['errors'],
                    'is_valid' => empty($validation['errors']),
                ]);

                if (empty($validation['errors'])) {
                    $validRecords++;
                } else {
                    $invalidRecords++;
                    $validationErrors = array_merge($validationErrors, $validation['errors']);
                }

                // Track unmapped departments
                if (!empty($validation['unmapped_department'])) {
                    $unmappedDepartments[] = $validation['unmapped_department'];
                }

            } catch (\Exception $e) {
                $invalidRecords++;
                $validationErrors[] = "Row {$totalRecords}: " . $e->getMessage();
                
                Log::warning('Failed to process attendance record', [
                    'import_id' => $import->id,
                    'row' => $totalRecords,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ]);
            }
        }

        fclose($handle);

        // Update import statistics
        $import->update([
            'total_records' => $totalRecords,
            'processed_records' => $totalRecords,
            'successful_records' => $validRecords,
            'failed_records' => $invalidRecords,
            'validation_errors' => array_unique($validationErrors),
            'unmapped_departments' => array_unique($unmappedDepartments),
        ]);
    }

    private function findValidHeaders($handle): array
    {
        $maxAttempts = 5; // Don't search beyond first 5 rows
        $attempts = 0;
        
        while ($attempts < $maxAttempts && ($row = fgetcsv($handle)) !== false) {
            $attempts++;
            
            // Skip empty rows or rows that are clearly title rows
            if (empty($row) || $this->isTitleRow($row)) {
                continue;
            }
            
            // Check if this row contains our required headers
            if ($this->hasRequiredHeaders($row)) {
                return $row;
            }
        }
        
        // If we reach here, no valid headers were found
        throw new \InvalidArgumentException('No valid headers found in CSV file. Expected headers: ' . implode(', ', self::REQUIRED_HEADERS));
    }
    
    private function isTitleRow(array $row): bool
    {
        // Check if this looks like a title row
        $firstCell = trim($row[0] ?? '');
        
        // Common title row patterns
        $titlePatterns = [
            'Total Time Card',
            'Attendance Report',
            'Time Report',
            'Export Report',
        ];
        
        foreach ($titlePatterns as $pattern) {
            if (stripos($firstCell, $pattern) !== false) {
                return true;
            }
        }
        
        // If first cell has content but most other cells are empty, it's likely a title
        $nonEmptyCells = count(array_filter($row, function($cell) {
            return !empty(trim($cell));
        }));
        
        return $nonEmptyCells <= 2 && !empty($firstCell);
    }
    
    private function hasRequiredHeaders(array $headers): bool
    {
        $foundHeaders = 0;
        foreach (self::REQUIRED_HEADERS as $required) {
            if (in_array($required, $headers)) {
                $foundHeaders++;
            }
        }
        
        // Consider it valid if we find at least 80% of required headers
        return $foundHeaders >= (count(self::REQUIRED_HEADERS) * 0.8);
    }

    private function validateHeaders(array $headers): void
    {
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        
        if (!empty($missingHeaders)) {
            throw new \InvalidArgumentException(
                'Missing required headers: ' . implode(', ', $missingHeaders)
            );
        }
    }

    private function parseRow(array $headers, array $row): array
    {
        return array_combine($headers, $row);
    }

    private function validateRecord(array $record, array $existingEmployeeIds, array $companyMappings): array
    {
        $errors = [];
        $employeeId = null;
        $companyId = null;
        $unmappedDepartment = null;

        // Validate employee ID exists
        $csvEmployeeId = $record['Employee ID'];
        if (in_array($csvEmployeeId, $existingEmployeeIds)) {
            $employee = Employee::where('employee_id', $csvEmployeeId)->first();
            $employeeId = $employee->id;
        } else {
            $errors[] = "Employee ID '{$csvEmployeeId}' not found in system";
        }

        // Map department to company
        $department = $record['Department'];
        if (isset($companyMappings[$department])) {
            $companyId = $companyMappings[$department];
        } else {
            $errors[] = "Department '{$department}' not mapped to any company";
            $unmappedDepartment = $department;
        }

        // Validate date format
        try {
            Carbon::createFromFormat('Y-m-d', $record['Date']);
        } catch (\Exception $e) {
            $errors[] = "Invalid date format: {$record['Date']}";
        }

        // Validate required fields
        if (empty($record['First Name'])) {
            $errors[] = "First Name is required";
        }

        if (empty($record['Check In']) && empty($record['Check Out'])) {
            $errors[] = "At least one of Check In or Check Out is required";
        }

        return [
            'employee_id' => $employeeId,
            'company_id' => $companyId,
            'errors' => $errors,
            'unmapped_department' => $unmappedDepartment,
        ];
    }

    private function parseTime(?string $time): ?string
    {
        if (empty($time) || $time === '--') {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    private function parseDuration(?string $duration): ?float
    {
        if (empty($duration) || $duration === '--') {
            return null;
        }

        // Handle formats like "8.5", "8:30", "8h 30m"
        if (is_numeric($duration)) {
            return (float) $duration;
        }

        if (preg_match('/(\d+):(\d+)/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            return $hours + ($minutes / 60);
        }

        return null;
    }

    private function queueForBayzatSync(AttendanceImport $import): void
    {
        // Group valid records by company
        $recordsByCompany = AttendanceImportRecord::where('attendance_import_id', $import->id)
            ->where('is_valid', true)
            ->whereNotNull('company_id')
            ->get()
            ->groupBy('company_id');

        foreach ($recordsByCompany as $companyId => $records) {
            // Create sync batch
            $batch = BayzatSyncBatch::create([
                'company_id' => $companyId,
                'attendance_import_id' => $import->id,
                'total_records' => $records->count(),
            ]);

            // Dispatch sync job
            ProcessBayzatSync::dispatch($batch);
        }
    }

    public function retryFailedRecords(AttendanceImport $import): void
    {
        $failedRecords = AttendanceImportRecord::where('attendance_import_id', $import->id)
            ->where('bayzat_sync_status', 'failed')
            ->where('bayzat_retry_count', '<', 5)
            ->get();

        $recordsByCompany = $failedRecords->groupBy('company_id');

        foreach ($recordsByCompany as $companyId => $records) {
            if (empty($companyId)) {
                continue;
            }

            // Reset records to pending
            foreach ($records as $record) {
                $record->resetForRetry();
            }

            // Create new sync batch
            $batch = BayzatSyncBatch::create([
                'company_id' => $companyId,
                'attendance_import_id' => $import->id,
                'total_records' => $records->count(),
            ]);

            // Dispatch sync job
            ProcessBayzatSync::dispatch($batch);
        }
    }
}
