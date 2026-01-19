<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZkDevice extends Model
{
    use HasFactory;

    protected $table = 'zk_devices';

    protected $fillable = [
        'serial_number',
        'name',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the raw attendance records for this device
     */
    public function attendanceRaw(): HasMany
    {
        return $this->hasMany(ZkAttendanceRaw::class, 'device_id');
    }

    /**
     * Get the daily attendance records for this device
     */
    public function dailyAttendance(): HasMany
    {
        return $this->hasMany(ZkDailyAttendance::class, 'device_id');
    }
}
