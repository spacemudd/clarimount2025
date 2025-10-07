<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AttendanceImportRecord;
use App\Models\BayzatSyncBatch;
use App\Models\Company;
use Illuminate\Console\Command;

class BayzatSyncStatus extends Command
{
    protected $signature = 'bayzat:sync-status 
                           {--company= : Show status for specific company ID}
                           {--detailed : Show detailed breakdown}';

    protected $description = 'Show Bayzat sync status and statistics';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $detailed = $this->option('detailed');

        $this->info('Bayzat Sync Status Report');
        $this->line('==========================');

        if ($companyId) {
            $this->showCompanyStatus($companyId, $detailed);
        } else {
            $this->showOverallStatus($detailed);
        }

        return 0;
    }

    private function showOverallStatus(bool $detailed): void
    {
        // Overall statistics
        $totalRecords = AttendanceImportRecord::count();
        $syncedRecords = AttendanceImportRecord::where('bayzat_sync_status', 'synced')->count();
        $failedRecords = AttendanceImportRecord::where('bayzat_sync_status', 'failed')->count();
        $pendingRecords = AttendanceImportRecord::where('bayzat_sync_status', 'pending')->count();

        $this->info('Overall Statistics:');
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Synced', $syncedRecords, $this->percentage($syncedRecords, $totalRecords)],
                ['Failed', $failedRecords, $this->percentage($failedRecords, $totalRecords)],
                ['Pending', $pendingRecords, $this->percentage($pendingRecords, $totalRecords)],
                ['Total', $totalRecords, '100.00%'],
            ]
        );

        // Batch statistics
        $totalBatches = BayzatSyncBatch::count();
        $completedBatches = BayzatSyncBatch::where('status', 'completed')->count();
        $failedBatches = BayzatSyncBatch::where('status', 'failed')->count();
        $pendingBatches = BayzatSyncBatch::where('status', 'pending')->count();

        $this->line('');
        $this->info('Batch Statistics:');
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Completed', $completedBatches, $this->percentage($completedBatches, $totalBatches)],
                ['Failed', $failedBatches, $this->percentage($failedBatches, $totalBatches)],
                ['Pending', $pendingBatches, $this->percentage($pendingBatches, $totalBatches)],
                ['Total', $totalBatches, '100.00%'],
            ]
        );

        if ($detailed) {
            $this->showCompanyBreakdown();
        }
    }

    private function showCompanyStatus(int $companyId, bool $detailed): void
    {
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Company with ID {$companyId} not found.");
            return;
        }

        $this->info("Status for Company: {$company->name} (ID: {$companyId})");

        $records = AttendanceImportRecord::where('company_id', $companyId);
        $totalRecords = $records->count();

        if ($totalRecords === 0) {
            $this->info('No attendance records found for this company.');
            return;
        }

        $syncedRecords = $records->where('bayzat_sync_status', 'synced')->count();
        $failedRecords = $records->where('bayzat_sync_status', 'failed')->count();
        $pendingRecords = $records->where('bayzat_sync_status', 'pending')->count();

        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Synced', $syncedRecords, $this->percentage($syncedRecords, $totalRecords)],
                ['Failed', $failedRecords, $this->percentage($failedRecords, $totalRecords)],
                ['Pending', $pendingRecords, $this->percentage($pendingRecords, $totalRecords)],
                ['Total', $totalRecords, '100.00%'],
            ]
        );

        if ($detailed && $failedRecords > 0) {
            $this->showFailedRecords($companyId);
        }
    }

    private function showCompanyBreakdown(): void
    {
        $this->line('');
        $this->info('Company Breakdown:');

        $companies = Company::whereHas('attendanceImportRecords')
            ->withCount([
                'attendanceImportRecords as total_records',
                'attendanceImportRecords as synced_records' => function ($query) {
                    $query->where('bayzat_sync_status', 'synced');
                },
                'attendanceImportRecords as failed_records' => function ($query) {
                    $query->where('bayzat_sync_status', 'failed');
                },
                'attendanceImportRecords as pending_records' => function ($query) {
                    $query->where('bayzat_sync_status', 'pending');
                },
            ])
            ->get();

        $this->table(
            ['Company', 'Total', 'Synced', 'Failed', 'Pending', 'Success Rate'],
            $companies->map(function ($company) {
                $successRate = $company->total_records > 0 
                    ? $this->percentage($company->synced_records, $company->total_records)
                    : '0.00%';

                return [
                    $company->name,
                    $company->total_records,
                    $company->synced_records,
                    $company->failed_records,
                    $company->pending_records,
                    $successRate,
                ];
            })->toArray()
        );
    }

    private function showFailedRecords(int $companyId): void
    {
        $this->line('');
        $this->info('Recent Failed Records:');

        $failedRecords = AttendanceImportRecord::where('company_id', $companyId)
            ->where('bayzat_sync_status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $this->table(
            ['Employee ID', 'Date', 'Retry Count', 'Last Error'],
            $failedRecords->map(function ($record) {
                return [
                    $record->csv_employee_id,
                    $record->date->format('Y-m-d'),
                    $record->bayzat_retry_count,
                    substr($record->bayzat_sync_error ?? '', 0, 50) . '...',
                ];
            })->toArray()
        );
    }

    private function percentage(int $part, int $total): string
    {
        if ($total === 0) {
            return '0.00%';
        }

        return number_format(($part / $total) * 100, 2) . '%';
    }
}
