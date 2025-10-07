<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\RetryFailedBayzatSync as RetryFailedBayzatSyncJob;
use App\Models\AttendanceImportRecord;
use Illuminate\Console\Command;

class RetryFailedBayzatSync extends Command
{
    protected $signature = 'bayzat:retry-failed 
                           {--company= : Retry for specific company ID}
                           {--max-retries=5 : Maximum number of retry attempts}
                           {--dry-run : Show what would be retried without actually retrying}';

    protected $description = 'Retry failed Bayzat sync records';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $maxRetries = (int) $this->option('max-retries');
        $dryRun = $this->option('dry-run');

        $this->info('Checking for failed Bayzat sync records...');

        // Build query
        $query = AttendanceImportRecord::where('bayzat_sync_status', 'failed')
            ->where('bayzat_retry_count', '<', $maxRetries)
            ->where(function ($q) {
                $q->whereNull('bayzat_next_retry_at')
                  ->orWhere('bayzat_next_retry_at', '<=', now());
            });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $failedRecords = $query->get();

        if ($failedRecords->isEmpty()) {
            $this->info('No failed records found to retry.');
            return 0;
        }

        $this->info("Found {$failedRecords->count()} failed records to retry.");

        if ($dryRun) {
            $this->table(
                ['Company ID', 'Employee ID', 'Date', 'Retry Count', 'Last Error'],
                $failedRecords->map(function ($record) {
                    return [
                        $record->company_id,
                        $record->csv_employee_id,
                        $record->date->format('Y-m-d'),
                        $record->bayzat_retry_count,
                        substr($record->bayzat_sync_error ?? '', 0, 50) . '...',
                    ];
                })->toArray()
            );

            $this->info('Dry run completed. Use without --dry-run to actually retry.');
            return 0;
        }

        // Dispatch retry job
        if ($companyId) {
            RetryFailedBayzatSyncJob::forCompany($companyId, $maxRetries);
            $this->info("Dispatched retry job for company {$companyId}.");
        } else {
            RetryFailedBayzatSyncJob::forAllCompanies($maxRetries);
            $this->info('Dispatched retry job for all companies.');
        }

        return 0;
    }
}
