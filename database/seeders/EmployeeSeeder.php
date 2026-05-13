<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create/update departments before employees so manager ownership can be linked.
        $departments = collect([
            ['HR', 'Human Resources', 'People operations and leave administration', 'hr@company.com'],
            ['IT', 'Information Technology', 'Systems and internal tech support', 'manager@test.com'],
            ['FIN', 'Finance', 'Payroll and accounting', 'elena.garcia@company.com'],
            ['OPS', 'Operations', 'Daily business coordination', null],
        ])->mapWithKeys(function ($department) {
            $manager = $department[3] ? User::where('email', $department[3])->first() : null;
            $created = Department::updateOrCreate(
                ['code' => $department[0]],
                [
                    'name' => $department[1], 
                    'description' => $department[2], 
                    'manager_user_id' => $manager?->id, 
                    'is_active' => true
                ]
            );

            return [$department[0] => $created];
        });

        $employees = [
            ['HR-0001', 'hr@company.com', 'Maria', 'Andres', 'HR', 'HR Administrator', 'female', '2018-01-05', 1500],
            ['MGR-0004', 'manager@test.com', 'Roberto', 'Cruz', 'IT', 'IT Manager', 'male', '2019-03-12', 1450],
            ['MGR-0005', 'elena.garcia@company.com', 'Elena', 'Garcia', 'FIN', 'Finance Manager', 'female', '2019-06-18', 1400],
            ['EMP-0012', 'juan.delacruz@company.com', 'Juan', 'dela Cruz', 'IT', 'Senior Developer', 'male', '2021-02-11', 1200],
            ['EMP-0018', 'sofia.lim@company.com', 'Sofia', 'Lim', 'FIN', 'Accountant', 'female', '2022-07-20', 1000],
            ['EMP-0021', 'renz.pascual@company.com', 'Renz', 'Pascual', 'OPS', 'Operations Lead', 'male', '2020-11-09', 900],
        ];

        foreach ($employees as $row) {
            $user = User::where('email', $row[1])->first();
            
            if ($user) {
                $department = $departments[$row[4]];
                Employee::updateOrCreate(
                    ['employee_id' => $row[0]],
                    [
                        'user_id' => $user->id,
                        'department_id' => $department->id,
                        'first_name' => $row[2],
                        'last_name' => $row[3],
                        'gender' => $row[6],
                        'department' => $department->name,
                        'position' => $row[5],
                        'date_hired' => $row[7],
                        'contact_info' => $user->email,
                        'phone' => '+63 917 123 4567',
                        'address' => 'Makati City',
                        'daily_rate' => $row[8],
                        'employment_status' => 'active',
                    ]
                );
            }
        }

        $hrUser = User::updateOrCreate(
            ['email' => 'hr_admin@test.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('hrpassword123'),
                'role' => 'hr_admin',
                'status' => 'active',
                'pending_employee_id' => null,
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $hrUser->id],
            [
                'employee_id' => 'HR-ADMIN-01',
                'first_name' => 'Admin',
                'last_name' => 'HR',
                'gender' => 'female',
                'department_id' => $departments['HR']->id,
                'department' => 'Human Resources',
                'position' => 'HR Head',
                'date_hired' => now(),
                'employment_status' => 'active',
                'daily_rate' => 2500,
                'contact_info' => 'hr_admin@test.com',
            ]
        );

        $staffUser = User::updateOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => 'Regular Staff',
                'password' => Hash::make('staffpassword123'),
                'role' => 'employee',
                'status' => 'active',
                'pending_employee_id' => null,
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $staffUser->id],
            [
                'employee_id' => 'EMP-STAFF-01',
                'first_name' => 'Regular',
                'last_name' => 'Staff',
                'gender' => 'male',
                'department_id' => $departments['IT']->id,
                'department' => 'Information Technology',
                'position' => 'Technical Support',
                'date_hired' => now(),
                'employment_status' => 'active',
                'daily_rate' => 800,
                'contact_info' => 'staff@test.com',
            ]
        );
    }
}
