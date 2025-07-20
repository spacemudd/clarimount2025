<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustodyChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'updated_by',
        'previous_state',
        'new_state',
        'changes_summary',
        'document_path',
        'status',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
    ];

    /**
     * Get the employee this custody change belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who updated the custody.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a summary of the changes made.
     */
    public function getChangesSummaryAttribute(): string
    {
        if (!empty($this->attributes['changes_summary'])) {
            return $this->attributes['changes_summary'];
        }

        $previousCount = count($this->previous_state['assets'] ?? []);
        $newCount = count($this->new_state['assets'] ?? []);
        
        $added = $newCount - $previousCount;
        
        if ($added > 0) {
            return trans_choice('messages.custody.added_assets', $added, ['count' => $added]);
        } elseif ($added < 0) {
            $removed = abs($added);
            return trans_choice('messages.custody.removed_assets', $removed, ['count' => $removed]);
        } else {
            return __('messages.custody.modified_asset_assignments');
        }
    }

    /**
     * Check if custody change is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if custody change is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'signed' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
