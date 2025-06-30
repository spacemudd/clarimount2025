<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AssetTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manufacturer',
        'model_name',
        'model_number',
        'asset_category_id',
        'company_id',
        'specifications',
        'default_notes',
        'is_global',
        'usage_count',
        'created_by_user_id',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_global' => 'boolean',
        'usage_count' => 'integer',
    ];

    protected $attributes = [
        'is_global' => true,
        'usage_count' => 0,
    ];

    protected $appends = [
        'display_name',
        'formatted_specifications',
    ];

    /**
     * Get the asset category this template belongs to.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get the company this template belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this template.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope for global templates.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for company-specific templates.
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for templates available to a specific company (global + company-specific).
     */
    public function scopeAvailableToCompany(Builder $query, int $companyId): Builder
    {
        return $query->where(function ($q) use ($companyId) {
            $q->where('is_global', true)
              ->orWhere('company_id', $companyId);
        });
    }

    /**
     * Scope for searching templates.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('manufacturer', 'like', "%{$search}%")
              ->orWhere('model_name', 'like', "%{$search}%")
              ->orWhere('model_number', 'like', "%{$search}%");
        });
    }

    /**
     * Increment usage count when template is used.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get display name for the template.
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->manufacturer,
            $this->model_name,
            $this->model_number ? "({$this->model_number})" : null
        ]);

        return $this->name ?: implode(' ', $parts);
    }

    /**
     * Get formatted specifications as a readable string.
     */
    public function getFormattedSpecificationsAttribute(): ?string
    {
        if (empty($this->specifications)) {
            return null;
        }

        $specs = [];
        foreach ($this->specifications as $key => $value) {
            $specs[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode(' • ', $specs);
    }

    /**
     * Check if template can be edited by user.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Global templates can only be edited by super admins
        if ($this->is_global) {
            return $user->hasRole('super-admin');
        }

        // Company templates can be edited by the creator or company owner
        return $this->created_by_user_id === $user->id || 
               ($this->company && $this->company->owner_id === $user->id);
    }

    /**
     * Get the most popular templates.
     */
    public static function popular(int $limit = 10): Builder
    {
        return static::orderBy('usage_count', 'desc')->limit($limit);
    }
}
