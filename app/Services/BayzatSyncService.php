<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AttendanceImportRecord;
use App\Models\BayzatConfig;
use App\Models\BayzatSyncBatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BayzatSyncService
{
    private int $maxRecordsPerRequest;
    private int $rateLimitDelaySeconds; // seconds

    public function __construct()
    {
        $this->maxRecordsPerRequest = (int) config('services.bayzat.max_records_per_request', 20);
        $this->rateLimitDelaySeconds = (int) config('services.bayzat.rate_limit_delay', 1);
    }

    public function syncBatch(BayzatSyncBatch $batch): void
    {
        try {
            $batch->markAsStarted();

            // Get company's Bayzat configuration
            $config = $batch->company->bayzatConfig;
            if (!$config || !$config->isActive()) {
                $batch->markAsFailed('Bayzat configuration not found or disabled for company');
                return;
            }

            // Get pending records for this batch
            $records = AttendanceImportRecord::where('attendance_import_id', $batch->attendance_import_id)
                ->where('company_id', $batch->company_id)
                ->where('bayzat_sync_status', 'pending')
                ->where('is_valid', true)
                ->get();

            if ($records->isEmpty()) {
                $batch->markAsCompleted();
                return;
            }

            $syncedCount = 0;
            $failedCount = 0;

            // Process records in chunks to respect API limits
            $chunks = $records->chunk($this->maxRecordsPerRequest);

            foreach ($chunks as $chunk) {
                try {
                    $this->syncRecordChunk($config, $chunk->toArray());
                    $syncedCount += $chunk->count();

                    // Mark records as synced
                    foreach ($chunk as $record) {
                        $record->markAsSynced();
                    }

                } catch (\Exception $e) {
                    $failedCount += $chunk->count();
                    
                    // Mark records as failed
                    foreach ($chunk as $record) {
                        $record->markAsFailed($e->getMessage());
                    }

                    Log::error('Bayzat sync chunk failed', [
                        'batch_id' => $batch->id,
                        'company_id' => $batch->company_id,
                        'chunk_size' => $chunk->count(),
                        'error' => $e->getMessage(),
                    ]);
                }

                // Update batch progress
                $batch->updateProgress($syncedCount, $failedCount);

                // Respect rate limiting - wait between requests
                if ($chunks->count() > 1) {
                    sleep($this->rateLimitDelaySeconds);
                }
            }

            $batch->markAsCompleted();

            Log::info('Bayzat sync batch completed', [
                'batch_id' => $batch->id,
                'company_id' => $batch->company_id,
                'synced' => $syncedCount,
                'failed' => $failedCount,
                'total' => $records->count(),
            ]);

        } catch (\Exception $e) {
            $batch->markAsFailed($e->getMessage());
            
            Log::error('Bayzat sync batch failed', [
                'batch_id' => $batch->id,
                'company_id' => $batch->company_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function syncRecordChunk(BayzatConfig $config, array $records): void
    {
        // Mark records as syncing
        foreach ($records as $record) {
            $record->markAsSyncing();
        }

        // Transform records to Bayzat format
        $bayzatRecords = [];
        foreach ($records as $record) {
            $bayzatRecords = array_merge($bayzatRecords, $record->toBayzatFormat());
        }

        if (empty($bayzatRecords)) {
            throw new \RuntimeException('No valid attendance records to sync');
        }

        // Send to Bayzat API
        $response = $this->sendToBayzat($config, $bayzatRecords);

        if (!$response['success']) {
            throw new \RuntimeException($response['error']);
        }
    }

    private function sendToBayzat(BayzatConfig $config, array $records): array
    {
        try {
            $payload = ['records' => $records];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $config->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($config->api_url, $payload);

            // Log the request for debugging
            Log::debug('Bayzat API request', [
                'url' => $config->api_url,
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            $errorMessage = $this->getErrorMessage($response);
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Bayzat API request failed', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'company_id' => $config->company_id,
            ]);

            return [
                'success' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
            ];
        }
    }

    private function getErrorMessage(\Illuminate\Http\Client\Response $response): string
    {
        $statusCode = $response->status();
        $body = $response->body();

        switch ($statusCode) {
            case 400:
                return "Bad Request: Invalid data structure - {$body}";
            case 401:
                return "Unauthorized: Invalid API key";
            case 429:
                return "Rate limit exceeded: Too many requests";
            case 500:
                return "Internal server error: {$body}";
            default:
                return "HTTP {$statusCode}: {$body}";
        }
    }

    public function testConnection(BayzatConfig $config): array
    {
        try {
            // Send a minimal test request
            $testPayload = [
                'records' => [
                    [
                        'empId' => 'test',
                        'type' => 'checkIn',
                        'time' => now()->format('Y-m-d H:i:s'),
                    ]
                ]
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $config->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($config->api_url, $testPayload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Invalid API key',
                ];
            }

            return [
                'success' => false,
                'message' => $this->getErrorMessage($response),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function retryFailedRecords(int $companyId, ?int $maxRetries = null): int
    {
        $query = AttendanceImportRecord::where('company_id', $companyId)
            ->where('bayzat_sync_status', 'failed')
            ->where(function ($q) {
                $q->whereNull('bayzat_next_retry_at')
                  ->orWhere('bayzat_next_retry_at', '<=', now());
            });

        if ($maxRetries !== null) {
            $query->where('bayzat_retry_count', '<', $maxRetries);
        }

        $failedRecords = $query->get();

        if ($failedRecords->isEmpty()) {
            return 0;
        }

        // Group by import to create batches
        $recordsByImport = $failedRecords->groupBy('attendance_import_id');

        foreach ($recordsByImport as $importId => $records) {
            // Reset records to pending
            foreach ($records as $record) {
                $record->resetForRetry();
            }

            // Create new sync batch
            $batch = BayzatSyncBatch::create([
                'company_id' => $companyId,
                'attendance_import_id' => $importId,
                'total_records' => $records->count(),
            ]);

            // Sync immediately (could also be queued)
            $this->syncBatch($batch);
        }

        return $failedRecords->count();
    }
}
