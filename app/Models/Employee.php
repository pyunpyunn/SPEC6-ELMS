<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    public const MANAGER_POSITION_IDS = [3000, 4000, 5000];

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'position_id',
        'manager_id',
        'first_name',
        'last_name',
        'gender',
        'date_hired',
        'contact_info',
        'phone',
        'address',
        'daily_rate',
        'employment_status',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'daily_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departmentRecord(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function positionRecord(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));

        return $name !== '' ? $name : $this->user?->name ?? 'Employee';
    }

    public function getDepartmentAttribute(): ?string
    {
        return $this->departmentRecord?->name;
    }

    public function getPositionAttribute(): ?string
    {
        return $this->positionRecord?->name;
    }

    public function departmentCode(): ?string
    {
        return $this->departmentRecord?->code;
    }

    public function positionCode(): ?int
    {
        $parts = explode('-', (string) $this->employee_id);

        if (isset($parts[1]) && is_numeric($parts[1])) {
            $positionCode = (int) $parts[1];
            $configuredPositionCodes = collect(config('positions.position_ids', []))
                ->flatMap(fn (array $positions) => array_map('intval', array_keys($positions)));

            if ($configuredPositionCodes->contains($positionCode)) {
                return $positionCode;
            }
        }

        $departmentCode = $this->departmentCode();

        return $departmentCode && $this->position
            ? self::positionCodeFor($departmentCode, $this->position)
            : null;
    }

    public function accessRole(): string
    {
        $departmentCode = $this->departmentCode();

        if ($departmentCode === 'HR') {
            return 'hr_admin';
        }

        if (in_array($this->positionCode(), self::MANAGER_POSITION_IDS, true) || str_contains(strtolower((string) $this->position), 'manager')) {
            return 'manager';
        }

        return 'employee';
    }

    public function isManagerPosition(): bool
    {
        return $this->accessRole() === 'manager';
    }

    public static function accessRoleFor(string|int $department, string $positionTitle): string
    {
        $departmentCode = self::departmentCodeFor($department);

        if ($departmentCode === 'HR') {
            return 'hr_admin';
        }

        return (in_array(self::positionCodeFor($departmentCode, $positionTitle), self::MANAGER_POSITION_IDS, true)
            || str_contains(strtolower($positionTitle), 'manager'))
            ? 'manager'
            : 'employee';
    }

    public static function positionCodeFor(string|int $department, string $positionTitle): ?int
    {
        $departmentCode = self::departmentCodeFor($department);
        $positionIds = config("positions.position_ids.{$departmentCode}", []);

        foreach ($positionIds as $id => $title) {
            if ($title === $positionTitle) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * Generate a new employee ID with format: DEPT_PREFIX-POSITION_ID-COUNT
     * Example: HR-2000-001, IT-3001-042, FIN-4001-003
     *
     * @param  string|int  $department  Department code (e.g., 'HR') or department ID
     * @param  string  $positionTitle  Position name (e.g., 'HR Administrator')
     * @return string  Formatted employee ID
     *
     * @throws \Exception If department or position not found in config
     */
    public static function generateEmployeeId(string|int $department, string $positionTitle): string
    {
        $departmentCode = self::departmentCodeFor($department);
        $departmentPrefixes = config('positions.department_prefixes', []);

        if (! isset($departmentPrefixes[$departmentCode])) {
            throw new \Exception("Department code '{$departmentCode}' not found in position configuration.");
        }

        $prefix = $departmentPrefixes[$departmentCode];
        $positionId = self::positionCodeFor($departmentCode, $positionTitle);

        if (! $positionId) {
            throw new \Exception("Position '{$positionTitle}' not found for department '{$departmentCode}'.");
        }

        $maxCount = self::query()
            ->where('employee_id', 'like', "{$prefix}-{$positionId}-%")
            ->pluck('employee_id')
            ->map(function (?string $employeeId): int {
                $parts = explode('-', (string) $employeeId);

                return isset($parts[2]) && is_numeric($parts[2]) ? (int) $parts[2] : 0;
            })
            ->max() ?? 0;

        return "{$prefix}-{$positionId}-".str_pad((string) ($maxCount + 1), 3, '0', STR_PAD_LEFT);
    }

    private static function departmentCodeFor(string|int $department): string
    {
        if (! is_numeric($department)) {
            return (string) $department;
        }

        $dept = Department::find($department);

        if (! $dept) {
            throw new \Exception("Department with ID {$department} not found.");
        }

        return $dept->code;
    }
}
