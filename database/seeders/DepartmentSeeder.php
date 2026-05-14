<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['HR', 'Human Resources', 'People operations and leave administration', 'hr@company.com'],
            ['IT', 'Information Technology', 'Systems and internal tech support', 'manager@test.com'],
            ['FIN', 'Finance', 'Payroll and accounting', 'elena.garcia@company.com'],
            ['OPS', 'Operations', 'Daily business coordination', null],
        ])->each(function (array $department): void {
            $manager = $department[3] ? User::where('email', $department[3])->first() : null;

            Department::updateOrCreate(
                ['code' => $department[0]],
                [
                    'name' => $department[1],
                    'description' => $department[2],
                    'manager_user_id' => $manager?->id,
                    'is_active' => true,
                ]
            );
        });
    }
}
