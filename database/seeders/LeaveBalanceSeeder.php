<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $leaveTypes = LeaveType::where('is_active', true)->get();

        Employee::where('employment_status', 'active')->get()->each(function (Employee $employee) use ($leaveTypes, $year): void {
            $leaveTypes->each(function (LeaveType $leaveType) use ($employee, $year): void {
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                    ],
                    [
                        'allocated_days' => (int) $leaveType->annual_allocation,
                        'used_days' => 0,
                    ]
                );
            });
        });
    }
}
