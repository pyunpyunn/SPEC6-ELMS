<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'pending_employee_id', 'department_id', 'position_id'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the employee profile associated with the user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the position ID from the employee profile ID.
     * Format: DEPT-POSITIONID-COUNT
     * Example: HR-2000-001 -> position_id = 2000
     */
    public function getPositionIdFromEmployeeIdAttribute(): int
    {
        return (int) ($this->employee?->positionCode() ?? 0);
    }

    public function derivedRole(): string
    {
        $roles = collect([$this->role, $this->employee?->accessRole()])
            ->filter()
            ->map(fn (string $role) => $role === 'hr' ? 'hr_admin' : $role);

        if ($roles->contains('hr_admin')) {
            return 'hr_admin';
        }

        if ($roles->contains('manager')) {
            return 'manager';
        }

        return 'employee';
    }

    /**
     * Check if user is an HR Admin.
     */
    public function isHR(): bool
    {
        return $this->derivedRole() === 'hr_admin';
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->derivedRole() === 'manager';
    }

    /**
     * Check if user is a regular employee.
     */
    public function isEmployee(): bool
    {
        return $this->derivedRole() === 'employee';
    }

    public function hasAccessRole(string $role): bool
    {
        return match ($role) {
            'hr', 'hr_admin' => $this->isHR(),
            'manager' => $this->isManager(),
            'employee' => $this->isEmployee(),
            default => $this->derivedRole() === $role,
        };
    }

    /**
     * Get the access level for determining route/view visibility.
     * Used by middleware and views to determine access level.
     *
     * @return string 'hr', 'manager', 'employee'
     */
    public function getAccessLevel(): string
    {
        return match ($this->derivedRole()) {
            'hr_admin' => 'hr',
            'manager' => 'manager',
            default => 'employee',
        };
    }
}
