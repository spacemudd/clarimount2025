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
     * Format: [Company Name Abbreviation]-[3-digit sequential number]
     * Example: "Advanced Line for Technology" -> ADTY-001, ADTY-002, ADTY-003...
     * Example: "Global Tech Solutions" -> GTSL-001, GTSL-002, GTSL-003...
     */
    protected static function generateUniqueAssetTag($companyId): string
    {
        // Get the company to extract name
        $company = Company::find($companyId);
        if (!$company) {
            // Fallback to company ID if company not found
            $companyPrefix = str_pad($companyId % 100, 2, '0', STR_PAD_LEFT);
        } else {
            // Generate company abbreviation from name
            $companyPrefix = static::generateCompanyAbbreviation($company->name_en);
        }
        
        // Pattern for this company's asset tags (e.g., "ADTY-" for Advanced Line for Technology)
        $pattern = $companyPrefix . '-';
        
        // Get the highest asset tag number for this company with this pattern
        $lastAsset = static::where('company_id', $companyId)
            ->where('asset_tag', 'like', $pattern . '%')
            ->whereRaw('asset_tag REGEXP ?', ["^{$companyPrefix}-[0-9]+$"])
            ->orderByRaw('LENGTH(asset_tag) DESC, asset_tag DESC')
            ->first();
        
        if ($lastAsset) {
            // Extract the number part after the dash and increment
            $lastNumber = intval(substr($lastAsset->asset_tag, strlen($companyPrefix) + 1));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1; // Start from 1 for each company
        }
        
        // Format as 3-digit number with leading zeros (001, 002, etc.)
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $assetTag = $pattern . $formattedNumber;
        
        // Ensure uniqueness (just in case)
        while (static::where('company_id', $companyId)->where('asset_tag', $assetTag)->exists()) {
            $nextNumber++;
            $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $assetTag = $pattern . $formattedNumber;
        }
        
        return $assetTag;
    }

    /**
     * Generate a 4-letter abbreviation from company name.
     * Examples:
     * "Advanced Line for Technology" -> "ADTY"
     * "Global Tech Solutions" -> "GTSL"
     * "Microsoft Corporation" -> "MSCR"
     * "ABC Company" -> "ABCC"
     */
    protected static function generateCompanyAbbreviation(string $companyName): string
    {
        // Remove common business words and clean the name
        $commonWords = ['corporation', 'corp', 'company', 'co', 'inc', 'incorporated', 'ltd', 'limited', 'llc', 'for', 'and', 'the', 'of', 'in', 'to'];
        $cleanName = str_replace($commonWords, '', strtolower($companyName));
        
        // Split into words and remove empty elements
        $words = array_filter(explode(' ', trim($cleanName)));
        
        if (count($words) >= 2) {
            // Take first 2 letters from first 2 words
            $abbreviation = substr($words[0], 0, 2) . substr($words[1], 0, 2);
        } elseif (count($words) == 1) {
            // Take first 4 letters if only one word
            $abbreviation = substr($words[0], 0, 4);
        } else {
            // Fallback: take first 4 letters of original name (alphanumeric only)
            $alphanumeric = preg_replace('/[^a-zA-Z0-9]/', '', $companyName);
            $abbreviation = substr($alphanumeric, 0, 4);
        }
        
        // Ensure it's exactly 4 characters, pad with 'X' if needed
        $abbreviation = str_pad($abbreviation, 4, 'X');
        
        return strtoupper(substr($abbreviation, 0, 4));
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
