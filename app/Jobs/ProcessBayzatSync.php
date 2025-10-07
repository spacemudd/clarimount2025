<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BayzatSyncBatch;
use App\Services\BayzatSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBayzatSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes
    public int $backoff = 60; // 1 minute backoff

    public function __construct(
        public BayzatSyncBatch $batch
    ) {
        $this->onQueue('bayzat-sync');
    }

    public function handle(BayzatSyncService $syncService): void
    {
        try {
            Log::info('Starting Bayzat sync batch', [
                'batch_id' => $this->batch->id,
                'company_id' => $this->batch->company_id,
                'total_records' => $this->batch->total_records,
            ]);

            $syncService->syncBatch($this->batch);

            Log::info('Bayzat sync batch completed successfully', [
                'batch_id' => $this->batch->id,
                'company_id' => $this->batch->company_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Bayzat sync batch job failed', [
                'batch_id' => $this->batch->id,
                'company_id' => $this->batch->company_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bayzat sync batch job failed permanently', [
            'batch_id' => $this->batch->id,
            'company_id' => $this->batch->company_id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Mark batch as failed if not already marked
        if (!$this->batch->hasFailed()) {
            $this->batch->markAsFailed('Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage());
        }
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }
}
