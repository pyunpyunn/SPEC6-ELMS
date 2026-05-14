<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_pending_user_and_notifies_hr(): void
    {
        User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr_admin',
            'status' => 'active',
        ]);

        $this->post('/register', [
            'name' => 'Pending Employee',
            'email' => 'pending@example.com',
            'employee_id' => 'EMP-0099',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'title' => 'Account validation pending',
            'type' => 'account_validation',
        ]);
    }

    public function test_employee_leave_application_notifies_manager_and_hr(): void
    {
        [$employeeUser, $managerUser, $hrUser, $leaveType] = $this->leaveFixture();

        $this->actingAs($employeeUser)
            ->post(route('employee.leaves.store'), [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addWeek()->toDateString(),
                'end_date' => now()->addWeek()->addDay()->toDateString(),
                'reason' => 'Family appointment',
            ])
            ->assertRedirect(route('employee.leaves.index'));

        $this->assertDatabaseHas('leave_applications', [
            'employee_id' => $employeeUser->employee->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $managerUser->id,
            'type' => 'leave_request',
            'title' => 'Leave request pending',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $hrUser->id,
            'type' => 'leave_request',
            'title' => 'Leave request pending',
        ]);
    }

    public function test_manager_review_notifies_employee_and_hr(): void
    {
        [$employeeUser, $managerUser, $hrUser, $leaveType] = $this->leaveFixture();

        $this->actingAs($employeeUser)->post(route('employee.leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDay()->toDateString(),
            'reason' => 'Family appointment',
        ]);

        SystemNotification::query()->delete();
        $leave = $employeeUser->employee->leaveApplications()->firstOrFail();

        $this->actingAs($managerUser)
            ->patch(route('manager.approvals.approve', $leave), [
                'remarks' => 'Approved.',
            ])
            ->assertRedirect(route('manager.approvals.index'));

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $employeeUser->id,
            'type' => 'leave_status',
            'title' => 'Leave request approved',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $hrUser->id,
            'type' => 'leave_status',
            'title' => 'Leave request approved',
        ]);
    }

    private function leaveFixture(): array
    {
        $department = Department::create([
            'code' => 'IT',
            'name' => 'Information Technology',
            'is_active' => true,
        ]);

        $hrUser = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr_admin',
            'status' => 'active',
        ]);
        Employee::create([
            'user_id' => $hrUser->id,
            'employee_id' => 'HR-0001',
            'department_id' => $department->id,
            'first_name' => 'HR',
            'last_name' => 'Admin',
            'department' => $department->name,
            'position' => 'HR Admin',
            'date_hired' => now(),
            'contact_info' => $hrUser->email,
        ]);

        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
        ]);
        $manager = Employee::create([
            'user_id' => $managerUser->id,
            'employee_id' => 'MGR-0001',
            'department_id' => $department->id,
            'first_name' => 'Manager',
            'last_name' => 'User',
            'department' => $department->name,
            'position' => 'IT Manager',
            'date_hired' => now(),
            'contact_info' => $managerUser->email,
        ]);

        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'status' => 'active',
        ]);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'employee_id' => 'EMP-0001',
            'department_id' => $department->id,
            'manager_id' => $manager->id,
            'first_name' => 'Employee',
            'last_name' => 'User',
            'department' => $department->name,
            'position' => 'Developer',
            'date_hired' => now(),
            'contact_info' => $employeeUser->email,
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Vacation Leave',
            'slug' => 'vacation-leave',
            'annual_allocation' => 15,
            'requires_approval' => true,
            'requires_proof' => false,
            'is_active' => true,
        ]);

        LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'allocated_days' => 15,
            'used_days' => 0,
        ]);

        return [$employeeUser->refresh(), $managerUser->refresh(), $hrUser->refresh(), $leaveType];
    }
}
