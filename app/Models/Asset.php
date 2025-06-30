<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'asset_tag',
        'serial_number',
        'service_tag_number',
        'finance_tag_number',
        'asset_category_id',
        'asset_template_id',
        'location_id',
        'company_id',
        'assigned_to',
        'assigned_date',
        'model_name',
        'model_number',
        'manufacturer',
        'status',
        'notes',
        'image_path',
    ];

    protected $casts = [
        'status' => 'string',
        'assigned_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($asset) {
            if (empty($asset->asset_tag)) {
                $asset->asset_tag = static::generateUniqueAssetTag($asset->company_id);
            }
        });
    }

    /**
     * Generate a unique sequential asset tag for the company.
     */
    protected static function generateUniqueAssetTag($companyId): string
    {
        // Get the highest asset tag number for this company
        $lastAsset = static::where('company_id', $companyId)
            ->whereRaw('asset_tag REGEXP "^[0-9]+$"') // Only numeric asset tags
            ->orderByRaw('CAST(asset_tag AS UNSIGNED) DESC')
            ->first();
        
        if ($lastAsset && is_numeric($lastAsset->asset_tag)) {
            $nextNumber = intval($lastAsset->asset_tag) + 1;
        } else {
            $nextNumber = 1; // Start from 1 for each company
        }
        
        // Ensure uniqueness within the company
        while (static::where('company_id', $companyId)->where('asset_tag', (string)$nextNumber)->exists()) {
            $nextNumber++;
        }
        
        return (string)$nextNumber;
    }

    /**
     * Get the template this asset was created from.
     */
    public function assetTemplate(): BelongsTo
    {
        return $this->belongsTo(AssetTemplate::class);
    }

    /**
     * Get the category this asset belongs to.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Alias for assetCategory relationship (for consistency with frontend)
     */
    public function category(): BelongsTo
    {
        return $this->assetCategory();
    }

    /**
     * Get the location this asset is at.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the company this asset belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee this asset is assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * Get all assignment history for this asset.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    /**
     * Get the asset's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [$this->asset_tag];
        
        if ($this->model_name) {
            $parts[] = $this->model_name;
        }
        
        if ($this->model_number) {
            $parts[] = "({$this->model_number})";
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Get the current active assignment.
     */
    public function currentAssignment()
    {
        return $this->assignments()->where('status', 'active')->first();
    }

    /**
     * Check if asset is assigned.
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned' || $this->assignments()->where('status', 'active')->exists();
    }

    /**
     * Scope for assets by company.
     */
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for assets by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get all available status values.
     */
    public static function getStatuses(): array
    {
        return ['available', 'assigned', 'maintenance', 'retired'];
    }
}
