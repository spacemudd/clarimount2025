<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZkAttendanceRaw extends Model
{
    use HasFactory;

    protected $table = 'zk_attendance_raw';

    protected $fillable = [
        'device_id',
        'device_pin',
        'punch_time',
        'verify_mode',
        'raw_line',
        'received_at',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'received_at' => 'datetime',
        'verify_mode' => 'integer',
    ];

    /**
     * Get the device that owns this attendance record
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ZkDevice::class, 'device_id');
    }
}
