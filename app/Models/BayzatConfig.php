<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BayzatConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'api_key',
        'api_url',
        'is_enabled',
        'sync_frequency',
        'last_sync_at',
        'settings',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'settings' => 'array',
        'is_enabled' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the company that owns this Bayzat configuration.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the configuration is enabled and has a valid API key.
     */
    public function isActive(): bool
    {
        return $this->is_enabled && !empty($this->api_key);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }
}
