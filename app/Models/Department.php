<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'description', 'manager_user_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Employee::class)
            ->where(function ($query) {
                $query->where('employee_id', 'like', '%-3000-%')
                    ->orWhere('employee_id', 'like', '%-4000-%')
                    ->orWhere('employee_id', 'like', '%-5000-%');
            });
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
