<?php

namespace Tests;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesElmsFixtures
{
    protected function createEmployeeProfile(
        string $email,
        string $employeeId,
        string $departmentCode,
        string $positionName,
        string $role = 'employee',
        string $gender = 'male',
        ?Employee $manager = null,
        float $dailyRate = 1000
    ): Employee {
        $department = Department::where('code', $departmentCode)->firstOrFail();
        $position = Position::firstOrCreate([
            'department_id' => $department->id,
            'name' => $positionName,
        ]);

        $user = User::create([
            'name' => str($email)->before('@')->replace('.', ' ')->title()->toString(),
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $parts = explode(' ', $user->name, 2);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'manager_id' => $manager?->id,
            'first_name' => $parts[0] ?? 'Test',
            'last_name' => $parts[1] ?? 'User',
            'gender' => $gender,
            'department' => $department->name,
            'position' => $position->name,
            'date_hired' => now()->subYear(),
            'contact_info' => $email,
            'daily_rate' => $dailyRate,
            'employment_status' => 'active',
        ]);

        LeaveType::where('is_active', true)->get()->each(fn (LeaveType $leaveType) => LeaveBalance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => now()->year,
            ],
            [
                'allocated_days' => (int) $leaveType->annual_allocation,
                'used_days' => 0,
            ]
        ));

        return $employee->refresh();
    }
}
