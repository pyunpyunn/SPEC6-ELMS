<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenderLeaveVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_employee_edit_persists_gender_and_profile_uses_updated_value(): void
    {
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@company.com')->firstOrFail();
        $employee = Employee::where('employee_id', 'EMP-STAFF-01')->firstOrFail();

        $this->actingAs($hr)->put(route('hr.employees.update', $employee), [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'gender' => 'female',
            'email' => 'staff.updated@test.com',
            'role' => 'employee',
            'employee_id' => $employee->employee_id,
            'department_id' => $employee->department_id,
            'manager_id' => $employee->manager_id,
            'position' => $employee->position,
            'date_hired' => $employee->date_hired->toDateString(),
            'phone' => $employee->phone,
            'address' => $employee->address,
            'contact_info' => null,
            'daily_rate' => $employee->daily_rate,
            'employment_status' => $employee->employment_status,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'gender' => 'female',
            'contact_info' => 'staff.updated@test.com',
        ]);

        $this->actingAs($employee->fresh()->user)
            ->get(route('employee.profile'))
            ->assertOk()
            ->assertSee('Female');
    }

    public function test_gender_controls_visible_leave_types_for_employee_portal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $male = Employee::where('employee_id', 'EMP-STAFF-01')->firstOrFail();
        $female = Employee::where('employee_id', 'EMP-0018')->firstOrFail();

        $this->actingAs($male->user)
            ->get(route('employee.leave.index'))
            ->assertOk()
            ->assertSee('Paternity Leave')
            ->assertDontSee('Maternity Leave');

        $this->actingAs($female->user)
            ->get(route('employee.leave.index'))
            ->assertOk()
            ->assertSee('Maternity Leave')
            ->assertDontSee('Paternity Leave');
    }

    public function test_hidden_gender_leave_type_cannot_be_submitted(): void
    {
        $this->seed(DatabaseSeeder::class);

        $male = Employee::where('employee_id', 'EMP-STAFF-01')->firstOrFail();
        $maternity = LeaveType::where('name', 'Maternity Leave')->firstOrFail();

        $this->actingAs($male->user)->post(route('employee.leave.store'), [
            'leave_type_id' => $maternity->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Not available for male employee.',
        ])->assertStatus(422);
    }
}
