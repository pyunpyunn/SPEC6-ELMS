<?php

namespace Tests\Feature;

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesElmsFixtures;
use Tests\TestCase;

class LeaveConfigurationRulesTest extends TestCase
{
    use CreatesElmsFixtures;
    use RefreshDatabase;

    public function test_leave_configuration_updates_drive_balances_approval_and_compensation(): void
    {
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@company.com')->firstOrFail();
        $employee = $this->createEmployeeProfile('staff@test.com', 'IT-3002-001', 'IT', 'Developer');
        $vacation = LeaveType::where('name', 'Vacation Leave')->firstOrFail();
        $sick = LeaveType::where('name', 'Sick Leave')->firstOrFail();

        $this->actingAs($hr)
            ->put(route('admin.leave-types.update', $vacation), $this->leaveTypePayload($vacation, [
                'annual_allocation' => 20,
                'requires_approval' => 0,
                'is_compensable' => 1,
            ]))
            ->assertSessionHas('success');

        $this->assertSame(20, LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $vacation->id)
            ->where('year', now()->year)
            ->firstOrFail()
            ->allocated_days);

        $start = now()->addWeek();
        while ($start->isWeekend()) {
            $start = $start->addDay();
        }
        $end = $start->copy()->addDay();
        while ($end->isWeekend()) {
            $end = $end->addDay();
        }

        $this->actingAs($employee->user)
            ->post(route('employee.leaves.store'), [
                'leave_type_id' => $vacation->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'reason' => 'Auto approval configured by HR.',
            ])
            ->assertRedirect(route('employee.leaves.index'));

        $leave = LeaveApplication::where('employee_id', $employee->id)
            ->where('leave_type_id', $vacation->id)
            ->firstOrFail();

        $this->assertSame('approved', $leave->status);

        $expectedWorkingDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isWeekend()) {
                $expectedWorkingDays++;
            }
        }
        $expectedWorkingDays = max(1, $expectedWorkingDays);

        $this->assertSame($expectedWorkingDays, (int) LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $vacation->id)
            ->where('year', now()->year)
            ->firstOrFail()
            ->used_days);

        $this->actingAs($employee->user)
            ->get(route('employee.reports'))
            ->assertOk()
            ->assertSee('₱18,000.00')
            ->assertDontSee('₱70,000.00');

        $this->actingAs($hr)
            ->put(route('admin.leave-types.update', $sick), $this->leaveTypePayload($sick, [
                'is_compensable' => 1,
            ]))
            ->assertSessionHas('success');

        $this->actingAs($employee->user)
            ->get(route('employee.reports'))
            ->assertOk()
            ->assertSee('₱33,000.00');
    }

    public function test_leave_configuration_proof_requirement_is_enforced(): void
    {
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@company.com')->firstOrFail();
        $employee = $this->createEmployeeProfile('staff@test.com', 'IT-3002-001', 'IT', 'Developer');
        $vacation = LeaveType::where('name', 'Vacation Leave')->firstOrFail();

        $this->actingAs($hr)
            ->put(route('admin.leave-types.update', $vacation), $this->leaveTypePayload($vacation, [
                'requires_proof' => 1,
                'proof_rules' => 'Proof is required for vacation leave.',
            ]))
            ->assertSessionHas('success');

        $start = now()->addWeek();
        while ($start->isWeekend()) {
            $start = $start->addDay();
        }

        $this->actingAs($employee->user)
            ->post(route('employee.leaves.store'), [
                'leave_type_id' => $vacation->id,
                'start_date' => $start->toDateString(),
                'end_date' => $start->toDateString(),
                'reason' => 'Configured proof is required.',
            ])
            ->assertSessionHasErrors('proof');
    }

    private function leaveTypePayload(LeaveType $leaveType, array $overrides = []): array
    {
        return array_merge([
            'name' => $leaveType->name,
            'annual_allocation' => $leaveType->annual_allocation,
            'requires_approval' => $leaveType->requires_approval ? 1 : 0,
            'is_compensable' => $leaveType->is_compensable ? 1 : 0,
            'requires_proof' => $leaveType->requires_proof ? 1 : 0,
            'proof_rules' => $leaveType->proof_rules,
            'is_active' => $leaveType->is_active ? 1 : 0,
        ], $overrides);
    }
}
