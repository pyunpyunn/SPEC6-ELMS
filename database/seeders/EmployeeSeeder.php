<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = collect([
            ['HR', 'Human Resources', 'People operations, verification, and leave administration', 'hr@company.com'],
            ['IT', 'Information Technology', 'Systems, software, and internal technology support', 'manager@test.com'],
            ['FIN', 'Finance', 'Payroll, accounting, and financial reporting', 'elena.garcia@company.com'],
            ['OPS', 'Operations', 'Daily business operations and coordination', null],
            ['MKT', 'Marketing', 'Campaigns, brand, and customer communications', null],
            ['ADM', 'Administration', 'Administrative services and office support', null],
        ])->mapWithKeys(function ($department) {
            $manager = $department[3] ? User::where('email', $department[3])->first() : null;
            $created = Department::updateOrCreate(
                ['code' => $department[0]],
                ['name' => $department[1], 'description' => $department[2], 'manager_user_id' => $manager?->id, 'is_active' => true]
            );

            return [$department[0] => $created];
        });

        $employees = [
            ['EMP-0001', 'hr@company.com', 'Maria', 'Andres', 'HR', 'HR Administrator', '2018-01-05', 1500],
            ['EMP-0004', 'manager@test.com', 'Roberto', 'Cruz', 'IT', 'IT Manager', '2019-03-12', 1450],
            ['EMP-0005', 'elena.garcia@company.com', 'Elena', 'Garcia', 'FIN', 'Finance Manager', '2019-06-18', 1400],
            ['EMP-0012', 'juan.delacruz@company.com', 'Juan', 'dela Cruz', 'IT', 'Senior Developer', '2021-02-11', 1200],
            ['EMP-0018', 'sofia.lim@company.com', 'Sofia', 'Lim', 'FIN', 'Accountant', '2022-07-20', 1000],
            ['EMP-0021', 'renz.pascual@company.com', 'Renz', 'Pascual', 'OPS', 'Operations Lead', '2020-11-09', 900],
        ];

        foreach ($employees as $row) {
            $user = User::where('email', $row[1])->first();
            $department = $departments[$row[4]];
            Employee::updateOrCreate(
                ['employee_id' => $row[0]],
                [
                    'user_id' => $user->id,
                    'department_id' => $department->id,
                    'first_name' => $row[2],
                    'last_name' => $row[3],
                    'department' => $department->name,
                    'position' => $row[5],
                    'date_hired' => $row[6],
                    'contact_info' => $user->email,
                    'phone' => '+63 917 123 4567',
                    'address' => 'Makati City',
                    'daily_rate' => $row[7],
                    'employment_status' => 'active',
                ]
            );
        }
    }
}
