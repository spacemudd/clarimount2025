<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePenalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'late_minutes',
        'violation_type',
        'repeat_number',
        'action_type',
        'action_value',
        'action_text',
        'reason_text',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'late_minutes' => 'integer',
        'repeat_number' => 'integer',
        'action_value' => 'integer',
    ];

    /**
     * Get the employee that this penalty belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope to filter by employee
     */
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to filter by year (calendar year)
     */
    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('attendance_date', $year);
    }

    /**
     * Scope to filter by violation type
     */
    public function scopeByViolationType($query, string $violationType)
    {
        return $query->where('violation_type', $violationType);
    }
}
