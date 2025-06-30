<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'building',
        'office_number',
        'company_id',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($location) {
            if (empty($location->code)) {
                $location->code = static::generateUniqueCode($location->company_id);
            }
        });
    }

    /**
     * Generate a unique location code for the company.
     */
    protected static function generateUniqueCode($companyId): string
    {
        $prefix = 'LOC';
        $counter = 1;
        
        do {
            $code = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $exists = static::where('company_id', $companyId)
                           ->where('code', $code)
                           ->exists();
            $counter++;
        } while ($exists);
        
        return $code;
    }

    /**
     * Get the company that owns this location.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all assets assigned to this location.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Check if location is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get formatted location name with building and office.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        
        if ($this->building) {
            $parts[] = "Building {$this->building}";
        }
        
        if ($this->office_number) {
            $parts[] = "Office {$this->office_number}";
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Get location display format.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code}: {$this->full_name}";
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
