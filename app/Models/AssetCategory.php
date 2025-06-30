<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class AssetCategory extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'color',
        'company_id',
        'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope categories by company for nested set operations.
     */
    protected function getScopeAttributes()
    {
        return ['company_id'];
    }

    /**
     * Get the company that owns this category.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all assets in this category.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'asset_category_id');
    }

    /**
     * Get the category's full path (for breadcrumbs).
     */
    public function getFullPathAttribute(): string
    {
        return $this->ancestors()->pluck('name')->push($this->name)->implode(' → ');
    }

    /**
     * Get the category's display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code ? "{$this->code}: {$this->name}" : $this->name;
    }

    /**
     * Scope for active categories only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories only.
     */
    public function scopeRoots($query)
    {
        return $query->whereIsRoot();
    }
}
