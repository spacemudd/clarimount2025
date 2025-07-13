<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'employee_id',
        'assigned_by',
        'assigned_date',
        'returned_date',
        'returned_by',
        'status',
        'assignment_notes',
        'return_notes',
        'condition_notes',
        'checklist_data',
        'assignment_document_path',
        'return_document_path',
        'custody_change_id',
    ];

    protected $casts = [
        'checklist_data' => 'array',
        'assigned_date' => 'date',
        'returned_date' => 'date',
    ];

    /**
     * Get the asset being assigned.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the employee receiving the asset.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who assigned the asset.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who processed the return.
     */
    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Get the custody change that this assignment belongs to.
     */
    public function custodyChange(): BelongsTo
    {
        return $this->belongsTo(CustodyChange::class);
    }

    /**
     * Check if assignment is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if asset is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Get assignment duration in days.
     */
    public function getDurationDaysAttribute(): int
    {
        $endDate = $this->returned_date ?? now();
        return $this->assigned_date->diffInDays($endDate);
    }

    /**
     * Scope for active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for returned assignments.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }
}
