<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nationality extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'name',
    ];

    /**
     * Get the localized name based on the current app locale
     */
    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get employees who have this nationality
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'nationality_id');
    }

    /**
     * Scope to get only active nationalities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by localized name
     */
    public function scopeOrderByName($query)
    {
        $locale = app()->getLocale();
        $nameColumn = $locale === 'ar' ? 'name_ar' : 'name_en';
        return $query->orderBy($nameColumn);
    }
}
