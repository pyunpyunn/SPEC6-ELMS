<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesElmsFixtures;
use Tests\TestCase;

class GenderLeaveVisibilityTest extends TestCase
{
    use CreatesElmsFixtures;
    use RefreshDatabase;

    public function test_hr_employee_edit_persists_gender_and_profile_uses_updated_value(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$employee] = $this->createGenderEmployees();

        $hr = User::where('email', 'hr@company.com')->firstOrFail();

        $this->actingAs($hr)->put(route('admin.employees.update', $employee), [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'gender' => 'female',
            'email' => 'staff.updated@test.com',
            'role' => 'employee',
            'employee_id' => $employee->employee_id,
            'department_id' => $employee->department_id,
            'position_id' => $employee->position_id,
            'manager_id' => $employee->manager_id,
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
        [$male, $female] = $this->createGenderEmployees();

        $this->actingAs($male->user)
            ->get(route('employee.leaves.index'))
            ->assertOk()
            ->assertSee('Paternity Leave')
            ->assertDontSee('Maternity Leave');

        $this->actingAs($female->user)
            ->get(route('employee.leaves.index'))
            ->assertOk()
            ->assertSee('Maternity Leave')
            ->assertDontSee('Paternity Leave');
    }

    public function test_hidden_gender_leave_type_cannot_be_submitted(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$male] = $this->createGenderEmployees();

        $maternity = LeaveType::where('name', 'Maternity Leave')->firstOrFail();

        $this->actingAs($male->user)->post(route('employee.leaves.store'), [
            'leave_type_id' => $maternity->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Not available for male employee.',
        ])->assertStatus(422);
    }

    public function test_gender_specific_leave_types_are_hidden_when_gender_is_not_male_or_female(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$employee] = $this->createGenderEmployees();

        $employee->update(['gender' => 'other']);

        $this->actingAs($employee->user)
            ->get(route('employee.leaves.index'))
            ->assertOk()
            ->assertDontSee('Maternity Leave')
            ->assertDontSee('Paternity Leave');

        $maternity = LeaveType::where('name', 'Maternity Leave')->firstOrFail();
        $paternity = LeaveType::where('name', 'Paternity Leave')->firstOrFail();

        $this->actingAs($employee->user)->post(route('employee.leaves.store'), [
            'leave_type_id' => $maternity->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Gender-specific leave should not be available.',
        ])->assertStatus(422);

        $this->actingAs($employee->user)->post(route('employee.leaves.store'), [
            'leave_type_id' => $paternity->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Gender-specific leave should not be available.',
        ])->assertStatus(422);
    }

    private function createGenderEmployees(): array
    {
        return [
            $this->createEmployeeProfile('male.staff@test.com', 'IT-3002-001', 'IT', 'Developer', 'employee', 'male'),
            $this->createEmployeeProfile('female.staff@test.com', 'OPS-5002-001', 'OPS', 'Operations Staff', 'employee', 'female'),
        ];
    }
}
