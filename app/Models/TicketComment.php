<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'type',
        'is_internal',
        'time_spent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_internal' => 'boolean',
    ];

    /**
     * Get the ticket this comment belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if comment is internal.
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Check if comment is public.
     */
    public function isPublic(): bool
    {
        return !$this->is_internal;
    }

    /**
     * Get time spent in hours.
     */
    public function getTimeSpentHoursAttribute(): float
    {
        return round($this->time_spent / 60, 2);
    }

    /**
     * Scope for public comments.
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal comments.
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Get comment type color for UI.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'comment' => 'blue',
            'status_change' => 'yellow',
            'assignment' => 'green',
            'resolution' => 'purple',
            'internal' => 'gray',
            default => 'blue',
        };
    }
}
