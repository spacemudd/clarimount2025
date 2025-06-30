<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'company_id',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($department) {
            if (empty($department->code)) {
                $department->code = static::generateUniqueCode($department->company_id);
            }
        });
    }

    /**
     * Generate a unique incremental code for the company.
     */
    protected static function generateUniqueCode($companyId): string
    {
        // Get the highest code number for this company
        $lastDepartment = static::where('company_id', $companyId)
            ->whereRaw('code REGEXP "^DEPT[0-9]+$"') // Only auto-generated codes
            ->orderByRaw('CAST(SUBSTRING(code, 5) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastDepartment && preg_match('/^DEPT(\d+)$/', $lastDepartment->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1; // Start from 1 for each company
        }
        
        $code = 'DEPT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness within the company
        while (static::where('company_id', $companyId)->where('code', $code)->exists()) {
            $nextNumber++;
            $code = 'DEPT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return $code;
    }

    /**
     * Get the company this department belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all employees in this department.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope for departments by company.
     */
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the department's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code}: {$this->name}";
    }
}
