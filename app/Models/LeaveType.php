<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'annual_allocation',
        'requires_approval',
        'is_compensable',
        'requires_proof',
        'proof_rules',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_compensable' => 'boolean',
        'requires_proof' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function isVisibleForGender(?string $gender): bool
    {
        $gender = strtolower((string) $gender);
        $name = strtolower($this->name);

        if (str_contains($name, 'maternity')) {
            return $gender === 'female';
        }

        if (str_contains($name, 'paternity')) {
            return $gender === 'male';
        }

        return true;
    }
}
