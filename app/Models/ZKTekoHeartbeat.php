<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZKTekoHeartbeat extends Model
{
    use HasFactory;

    protected $table = 'zkteko_heartbeats';

    protected $fillable = [
        'device_id',
        'data',
        'timestamp',
    ];

    protected $casts = [
        'data' => 'array',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the device that owns the heartbeat
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ZKTekoDevice::class, 'device_id');
    }
}