<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BayzatSyncBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'attendance_import_id',
        'total_records',
        'synced_records',
        'failed_records',
        'status',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the company this batch belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the attendance import this batch belongs to.
     */
    public function attendanceImport(): BelongsTo
    {
        return $this->belongsTo(AttendanceImport::class);
    }

    /**
     * Check if the batch is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the batch is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the batch is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the batch has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark the batch as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the batch as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the batch as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $error,
        ]);
    }

    /**
     * Update sync progress.
     */
    public function updateProgress(int $synced, int $failed): void
    {
        $this->update([
            'synced_records' => $synced,
            'failed_records' => $failed,
        ]);
    }

    /**
     * Get the success rate as a percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0.0;
        }

        return round(($this->synced_records / $this->total_records) * 100, 2);
    }

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0.0;
        }

        $completed = $this->synced_records + $this->failed_records;
        return round(($completed / $this->total_records) * 100, 2);
    }
}
