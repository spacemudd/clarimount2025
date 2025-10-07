<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZKTekoAttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'zkteko_attendance_records';

    protected $fillable = [
        'device_id',
        'employee_id',
        'timestamp',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the device that owns the attendance record
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ZKTekoDevice::class, 'device_id');
    }

    /**
     * Get the employee associated with this record
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}