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
        'gender',
        'requires_approval',
        'is_compensable',
        'requires_proof',
        'proof_rules',
        'max_document_days',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_compensable' => 'boolean',
        'requires_proof' => 'boolean',
        'max_document_days' => 'integer',
        'is_active' => 'boolean',
        'gender' => 'string',
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
        // If leave type has specific gender requirement, check it
        if ($this->gender) {
            return strtolower((string) $gender) === strtolower($this->gender);
        }

        // Legacy support: check name for maternity/paternity if no gender is set
        $nameCheck = strtolower($this->name);
        if (str_contains($nameCheck, 'maternity')) {
            return strtolower((string) $gender) === 'female';
        }
        if (str_contains($nameCheck, 'paternity')) {
            return strtolower((string) $gender) === 'male';
        }

        // Available for all genders if no restrictions
        return true;
    }
}
