<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'specifications',
        'default_notes',
        'image_path',
        'usage_count',
        'created_by_user_id',
    ];

    protected $casts = [
        'specifications' => 'array',
        'usage_count' => 'integer',
    ];

    protected $attributes = [
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
     * Get the user who created this template.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get all assets that use this template.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
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
        // Templates can be edited by super admins or the creator
        return $user->hasRole('super-admin') || $this->created_by_user_id === $user->id;
    }

    /**
     * Get the most popular templates.
     */
    public static function popular(int $limit = 10): Builder
    {
        return static::orderBy('usage_count', 'desc')->limit($limit);
    }
}
