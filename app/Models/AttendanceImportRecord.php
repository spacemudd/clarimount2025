<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceImportRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_import_id',
        'employee_id',
        'company_id',
        'csv_employee_id',
        'first_name',
        'fingerprint_department',
        'date',
        'weekday',
        'check_in',
        'check_out',
        'clock_in',
        'clock_out',
        'work_duration',
        'break_duration',
        'overtime_duration',
        'bayzat_sync_status',
        'bayzat_sync_at',
        'bayzat_sync_error',
        'bayzat_retry_count',
        'bayzat_next_retry_at',
        'validation_errors',
        'is_valid',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
        'work_duration' => 'decimal:2',
        'break_duration' => 'decimal:2',
        'overtime_duration' => 'decimal:2',
        'bayzat_sync_at' => 'datetime',
        'bayzat_next_retry_at' => 'datetime',
        'validation_errors' => 'array',
        'is_valid' => 'boolean',
    ];

    /**
     * Get the attendance import this record belongs to.
     */
    public function attendanceImport(): BelongsTo
    {
        return $this->belongsTo(AttendanceImport::class);
    }

    /**
     * Get the employee this record belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the company this record belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the record is pending sync.
     */
    public function isPendingSync(): bool
    {
        return $this->bayzat_sync_status === 'pending';
    }

    /**
     * Check if the record is currently syncing.
     */
    public function isSyncing(): bool
    {
        return $this->bayzat_sync_status === 'syncing';
    }

    /**
     * Check if the record has been synced successfully.
     */
    public function isSynced(): bool
    {
        return $this->bayzat_sync_status === 'synced';
    }

    /**
     * Check if the record sync has failed.
     */
    public function hasSyncFailed(): bool
    {
        return $this->bayzat_sync_status === 'failed';
    }

    /**
     * Check if the record is skipped from sync.
     */
    public function isSkipped(): bool
    {
        return $this->bayzat_sync_status === 'skipped';
    }

    /**
     * Mark the record as syncing.
     */
    public function markAsSyncing(): void
    {
        $this->update([
            'bayzat_sync_status' => 'syncing',
        ]);
    }

    /**
     * Mark the record as synced successfully.
     */
    public function markAsSynced(): void
    {
        $this->update([
            'bayzat_sync_status' => 'synced',
            'bayzat_sync_at' => now(),
            'bayzat_sync_error' => null,
        ]);
    }

    /**
     * Mark the record sync as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'bayzat_sync_status' => 'failed',
            'bayzat_sync_error' => $error,
            'bayzat_retry_count' => $this->bayzat_retry_count + 1,
            'bayzat_next_retry_at' => $this->calculateNextRetryAt(),
        ]);
    }

    /**
     * Mark the record as skipped.
     */
    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'bayzat_sync_status' => 'skipped',
            'bayzat_sync_error' => $reason,
        ]);
    }

    /**
     * Reset the record for retry.
     */
    public function resetForRetry(): void
    {
        $this->update([
            'bayzat_sync_status' => 'pending',
            'bayzat_sync_error' => null,
            'bayzat_next_retry_at' => null,
        ]);
    }

    /**
     * Calculate the next retry time using exponential backoff.
     */
    private function calculateNextRetryAt(): \Carbon\Carbon
    {
        $delays = [5, 30, 120, 480, 1440]; // 5min, 30min, 2hr, 8hr, 24hr in minutes
        $delayIndex = min($this->bayzat_retry_count, count($delays) - 1);
        $delayMinutes = $delays[$delayIndex];

        return now()->addMinutes($delayMinutes);
    }

    /**
     * Check if the record can be retried.
     */
    public function canRetry(): bool
    {
        return $this->bayzat_retry_count < 5 && 
               $this->hasSyncFailed() && 
               ($this->bayzat_next_retry_at === null || $this->bayzat_next_retry_at->isPast());
    }

    /**
     * Transform the record to Bayzat API format.
     */
    public function toBayzatFormat(): array
    {
        $records = [];

        // Add check-in record if available
        if ($this->check_in) {
            $records[] = [
                'empId' => $this->csv_employee_id,
                'type' => 'checkIn',
                'time' => $this->date->format('Y-m-d') . ' ' . $this->check_in->format('H:i:s'),
            ];
        }

        // Add check-out record if available
        if ($this->check_out) {
            $records[] = [
                'empId' => $this->csv_employee_id,
                'type' => 'checkOut',
                'time' => $this->date->format('Y-m-d') . ' ' . $this->check_out->format('H:i:s'),
            ];
        }

        return $records;
    }
}
