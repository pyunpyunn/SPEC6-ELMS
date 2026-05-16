<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $this->profile('hr@company.com', 'HR-2000-001', 'HR', 'HR Administrator', [
            'first_name' => 'Kathleen',
            'last_name' => 'Barro',
            'gender' => 'female',
            'daily_rate' => 0,
        ]);
    }

    private function profile(string $email, string $employeeId, string $departmentCode, string $positionName, array $attributes): ?Employee
    {
        $user = User::where('email', $email)->first();
        $department = Department::where('code', $departmentCode)->first();

        if (! $user || ! $department) {
            return null;
        }

        $position = Position::firstOrCreate([
            'department_id' => $department->id,
            'name' => $positionName,
        ]);

        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_id' => $employeeId,
                'department_id' => $department->id,
                'position_id' => $position->id,
                'manager_id' => $attributes['manager_id'] ?? null,
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'],
                'gender' => $attributes['gender'] ?? null,
                'department' => $department->name,
                'position' => $position->name,
                'date_hired' => $attributes['date_hired'] ?? now()->subYear(),
                'employment_status' => 'active',
                'daily_rate' => $attributes['daily_rate'] ?? 1000,
                'contact_info' => $user->email,
            ]
        );

        $user->update([
            'role' => $employee->accessRole(),
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        return $employee;
    }
}
