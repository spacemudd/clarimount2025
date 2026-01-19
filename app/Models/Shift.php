<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'grace_minutes' => 'integer',
    ];

    /**
     * Get the workdays for this shift
     */
    public function workdays(): HasMany
    {
        return $this->hasMany(ShiftWorkday::class, 'shift_id');
    }

    /**
     * Get the employees assigned to this shift
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }
}
