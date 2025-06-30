<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'department',
        'job_title',
        'manager',
        'hire_date',
        'termination_date',
        'employment_status',
        'company_id',
        'notes',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    protected $appends = [
        'full_name',
        'display_name',
    ];

    /**
     * Get the company this employee belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all assets assigned to this employee.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    /**
     * Get all asset assignments for this employee.
     */
    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    /**
     * Get all tickets reported by this employee.
     */
    public function reportedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'reporter_id');
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the employee's display name (ID + Name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->employee_id . ' - ' . $this->full_name;
    }

    /**
     * Scope for active employees only.
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    /**
     * Check if employee is active.
     */
    public function isActive(): bool
    {
        return $this->employment_status === 'active';
    }
}
