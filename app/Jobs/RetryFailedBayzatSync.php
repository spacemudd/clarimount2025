<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AttendanceImportRecord;
use App\Models\BayzatSyncBatch;
use App\Services\BayzatSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedBayzatSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Only try once, as this is already a retry
    public int $timeout = 600; // 10 minutes

    public function __construct(
        public ?int $companyId = null,
        public ?int $maxRetries = 5
    ) {
        $this->onQueue('bayzat-retry');
    }

    public function handle(BayzatSyncService $syncService): void
    {
        try {
            if ($this->companyId) {
                // Retry for specific company
                $retriedCount = $syncService->retryFailedRecords($this->companyId, $this->maxRetries);
                
                Log::info('Retried failed Bayzat sync records for company', [
                    'company_id' => $this->companyId,
                    'retried_count' => $retriedCount,
                ]);
            } else {
                // Retry for all companies
                $totalRetried = 0;
                
                $failedRecords = AttendanceImportRecord::where('bayzat_sync_status', 'failed')
                    ->where('bayzat_retry_count', '<', $this->maxRetries)
                    ->where(function ($q) {
                        $q->whereNull('bayzat_next_retry_at')
                          ->orWhere('bayzat_next_retry_at', '<=', now());
                    })
                    ->select('company_id')
                    ->distinct()
                    ->pluck('company_id');

                foreach ($failedRecords as $companyId) {
                    if ($companyId) {
                        $retriedCount = $syncService->retryFailedRecords($companyId, $this->maxRetries);
                        $totalRetried += $retriedCount;
                    }
                }

                Log::info('Retried failed Bayzat sync records for all companies', [
                    'total_retried' => $totalRetried,
                    'companies_affected' => $failedRecords->count(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to retry Bayzat sync records', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Retry failed Bayzat sync job failed', [
            'company_id' => $this->companyId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Dispatch retry job for a specific company.
     */
    public static function forCompany(int $companyId, int $maxRetries = 5): void
    {
        static::dispatch($companyId, $maxRetries);
    }

    /**
     * Dispatch retry job for all companies.
     */
    public static function forAllCompanies(int $maxRetries = 5): void
    {
        static::dispatch(null, $maxRetries);
    }
}
