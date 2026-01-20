<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborLawRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_type',
        'min_minutes',
        'max_minutes',
        'repeat_number',
        'action_type',
        'action_value',
        'reason_text',
    ];

    protected $casts = [
        'min_minutes' => 'integer',
        'max_minutes' => 'integer',
        'repeat_number' => 'integer',
        'action_value' => 'integer',
    ];

    /**
     * Scope to filter by violation type
     */
    public function scopeByViolationType($query, string $violationType)
    {
        return $query->where('violation_type', $violationType);
    }

    /**
     * Scope to filter by repeat number
     */
    public function scopeByRepeatNumber($query, int $repeatNumber)
    {
        return $query->where('repeat_number', $repeatNumber);
    }
}
