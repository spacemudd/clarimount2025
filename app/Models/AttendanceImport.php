<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_path',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'validation_errors',
        'unmapped_departments',
        'status',
        'started_at',
        'completed_at',
        'user_id',
        'team_id',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'unmapped_departments' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'success_rate',
    ];

    /**
     * Get the user who initiated this import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team this import belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all import records for this import.
     */
    public function records(): HasMany
    {
        return $this->hasMany(AttendanceImportRecord::class);
    }

    /**
     * Get all sync batches for this import.
     */
    public function syncBatches(): HasMany
    {
        return $this->hasMany(BayzatSyncBatch::class);
    }

    /**
     * Get valid records only.
     */
    public function validRecords(): HasMany
    {
        return $this->hasMany(AttendanceImportRecord::class)->where('is_valid', true);
    }

    /**
     * Get invalid records only.
     */
    public function invalidRecords(): HasMany
    {
        return $this->hasMany(AttendanceImportRecord::class)->where('is_valid', false);
    }

    /**
     * Check if the import is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the import is in progress.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the import has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get the success rate as a percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if (!$this->total_records || $this->total_records === 0) {
            return 0.0;
        }

        return round((($this->successful_records ?? 0) / $this->total_records) * 100, 2);
    }

    /**
     * Mark the import as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the import as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the import as failed.
     */
    public function markAsFailed(string $error = null): void
    {
        $validationErrors = $this->validation_errors ?? [];
        if ($error) {
            $validationErrors[] = $error;
        }

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'validation_errors' => $validationErrors,
        ]);
    }
}
