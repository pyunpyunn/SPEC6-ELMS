<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positionsByDepartmentCode = [
            'HR' => ['HR Administrator', 'HR Specialist', 'HR Officer', 'HR Head'],
            'IT' => ['IT Manager', 'Senior Developer', 'Developer', 'QA Engineer', 'Technical Support'],
            'FIN' => ['Finance Manager', 'Accountant', 'Financial Analyst'],
            'OPS' => ['Operations Manager', 'Operations Lead', 'Operations Staff'],
        ];

        Department::query()
            ->whereIn('code', array_keys($positionsByDepartmentCode))
            ->get()
            ->each(function (Department $department) use ($positionsByDepartmentCode): void {
                foreach ($positionsByDepartmentCode[$department->code] as $positionName) {
                    Position::updateOrCreate(
                        ['department_id' => $department->id, 'name' => $positionName],
                        []
                    );
                }
            });
    }
}
