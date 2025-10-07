<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ZKTekoDevice extends Model
{
    use HasFactory;

    protected $table = 'zkteko_devices';

    protected $fillable = [
        'serial_number',
        'device_name',
        'model',
        'firmware_version',
        'ip_address',
        'mac_address',
        'device_info',
        'last_heartbeat',
        'last_attendance_record',
        'total_heartbeats',
        'total_attendance_records',
        'is_online',
        'status',
        'last_error',
    ];

    protected $casts = [
        'device_info' => 'array',
        'last_heartbeat' => 'datetime',
        'last_attendance_record' => 'datetime',
        'is_online' => 'boolean',
        'total_heartbeats' => 'integer',
        'total_attendance_records' => 'integer',
    ];

    /**
     * Get the device's heartbeat records
     */
    public function heartbeats(): HasMany
    {
        return $this->hasMany(ZKTekoHeartbeat::class, 'device_id');
    }

    /**
     * Get the device's attendance records
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(ZKTekoAttendanceRecord::class, 'device_id');
    }

    /**
     * Update device heartbeat
     */
    public function updateHeartbeat(array $data = []): void
    {
        $this->update([
            'last_heartbeat' => now(),
            'total_heartbeats' => $this->total_heartbeats + 1,
            'is_online' => true,
            'status' => 'online',
            'last_error' => null,
            'ip_address' => $data['ip_address'] ?? $this->ip_address,
            'device_info' => array_merge($this->device_info ?? [], $data),
        ]);

        // Create heartbeat record
        $this->heartbeats()->create([
            'device_id' => $this->id,
            'data' => $data,
            'timestamp' => now(),
        ]);
    }

    /**
     * Update device attendance record
     */
    public function updateAttendanceRecord(array $data = []): void
    {
        $this->update([
            'last_attendance_record' => now(),
            'total_attendance_records' => $this->total_attendance_records + 1,
            'is_online' => true,
            'status' => 'online',
            'last_error' => null,
        ]);

        // Create attendance record
        $this->attendanceRecords()->create([
            'device_id' => $this->id,
            'employee_id' => $data['employee_id'] ?? null,
            'timestamp' => $data['timestamp'] ?? now(),
            'type' => $data['type'] ?? 'unknown',
            'data' => $data,
        ]);
    }

    /**
     * Mark device as offline
     */
    public function markOffline(string $reason = null): void
    {
        $this->update([
            'is_online' => false,
            'status' => 'offline',
            'last_error' => $reason,
        ]);
    }

    /**
     * Check if device is considered offline (no heartbeat for 5 minutes)
     */
    public function isConsideredOffline(): bool
    {
        if (!$this->last_heartbeat) {
            return true;
        }

        return $this->last_heartbeat->diffInMinutes(now()) > 5;
    }

    /**
     * Get device display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->device_name ?: $this->serial_number;
    }

    /**
     * Get device status with color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'online' => 'green',
            'offline' => 'red',
            'error' => 'orange',
            'maintenance' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get time since last heartbeat
     */
    public function getTimeSinceLastHeartbeatAttribute(): string
    {
        if (!$this->last_heartbeat) {
            return 'Never';
        }

        return $this->last_heartbeat->diffForHumans();
    }

    /**
     * Scope for online devices
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Scope for offline devices
     */
    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    /**
     * Scope for devices with recent heartbeats
     */
    public function scopeWithRecentHeartbeats($query, int $minutes = 5)
    {
        return $query->where('last_heartbeat', '>=', now()->subMinutes($minutes));
    }
}