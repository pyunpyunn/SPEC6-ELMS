<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create HR employee profile (HR user was created in UserSeeder)
        $hrDept = Department::where('code', 'HR')->first();
        $hrUser = User::where('email', 'hr@company.com')->first();
        
        if ($hrDept && $hrUser) {
            $hrPosition = Position::firstOrCreate([
                'department_id' => $hrDept->id,
                'name' => 'HR Administrator',
            ]);

            Employee::updateOrCreate(
                ['user_id' => $hrUser->id],
                [
                    'employee_id' => 'HR-0001',
                    'first_name' => 'HR',
                    'last_name' => 'Administrator',
                    'gender' => 'female',
                    'department_id' => $hrDept->id,
                    'position_id' => $hrPosition->id,
                    'department' => $hrDept->name,
                    'position' => 'HR Administrator',
                    'date_hired' => now(),
                    'employment_status' => 'active',
                    'daily_rate' => 0,
                    'contact_info' => 'hr@company.com',
                ]
            );
        }
    }
}
