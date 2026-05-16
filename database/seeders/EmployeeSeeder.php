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
            'first_name' => 'HR',
            'last_name' => 'Administrator',
            'gender' => 'female',
            'daily_rate' => 0,
        ]);

        $itManager = $this->profile('manager@test.com', 'IT-3000-001', 'IT', 'IT Manager', [
            'first_name' => 'IT',
            'last_name' => 'Manager',
            'gender' => 'male',
            'daily_rate' => 1500,
        ]);

        $this->profile('elena.garcia@company.com', 'FIN-4000-001', 'FIN', 'Finance Manager', [
            'first_name' => 'Elena',
            'last_name' => 'Garcia',
            'gender' => 'female',
            'daily_rate' => 1500,
        ]);

        $this->profile('staff@test.com', 'IT-3002-001', 'IT', 'Developer', [
            'first_name' => 'Staff',
            'last_name' => 'Employee',
            'gender' => 'male',
            'daily_rate' => 1000,
            'manager_id' => $itManager?->id,
        ]);

        $this->profile('female.staff@test.com', 'OPS-5002-001', 'OPS', 'Operations Staff', [
            'first_name' => 'Female',
            'last_name' => 'Staff',
            'gender' => 'female',
            'daily_rate' => 1000,
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
