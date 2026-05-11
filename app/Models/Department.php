<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'description', 'manager_user_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
