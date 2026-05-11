<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year;
        Employee::all()->each(function (Employee $employee) use ($year) {
            LeaveType::all()->each(fn (LeaveType $type) => LeaveBalance::updateOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $year],
                ['allocated_days' => $type->annual_allocation]
            ));
        });

        $samples = [
            ['juan.delacruz@company.com', 'Sick Leave', now()->subDay(), now()->addDay(), 3, 'pending', 'Fever and flu. Medical certificate attached.'],
            ['sofia.lim@company.com', 'Vacation Leave', now()->addDays(5), now()->addDays(9), 5, 'pending', 'Family trip already booked.'],
            ['renz.pascual@company.com', 'Special Emergency Leave', now()->subDays(2), now()->subDay(), 2, 'approved', 'Family emergency.'],
            ['hr@company.com', 'Vacation Leave', now()->addDays(14), now()->addDays(16), 3, 'approved', 'Planned rest days.'],
        ];

        $reviewer = User::where('email', 'hr@company.com')->first();
        foreach ($samples as $sample) {
            $employee = User::where('email', $sample[0])->first()?->employee;
            $type = LeaveType::where('name', $sample[1])->first();
            $leave = LeaveApplication::updateOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'start_date' => $sample[2]->toDateString()],
                [
                    'end_date' => $sample[3]->toDateString(),
                    'total_days' => $sample[4],
                    'reason' => $sample[6],
                    'status' => $sample[5],
                    'remarks' => $sample[5] === 'approved' ? 'Approved by HR.' : null,
                    'reviewed_by' => $sample[5] === 'approved' ? $reviewer?->id : null,
                    'reviewed_at' => $sample[5] === 'approved' ? now() : null,
                ]
            );

            if ($leave->status === 'approved') {
                LeaveBalance::where('employee_id', $employee->id)->where('leave_type_id', $type->id)->where('year', $year)->update(['used_days' => $sample[4]]);
            }
        }

        $hr = User::where('email', 'hr@company.com')->first();
        SystemNotification::updateOrCreate(
            ['user_id' => $hr->id, 'title' => 'Pending user registrations'],
            ['body' => 'New registered accounts are waiting for HR verification.', 'type' => 'warning', 'action_url' => route('hr.users.pending')]
        );
    }
}
