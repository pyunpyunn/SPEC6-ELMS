<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleBasedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_login_with_employee_id_and_is_sent_to_admin_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'HR-2000-001',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertSame(url('/home'), $response->headers->get('Location'));

        $home = $this->get('/home');
        $home->assertStatus(302);
        $this->assertSame(route('admin.dashboard'), $home->headers->get('Location'));
    }

    public function test_employee_can_login_with_employee_id_and_is_sent_to_employee_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'IT-3002-001',
            'password' => 'staffpassword123',
        ]);

        $response->assertStatus(302);
        $this->assertSame(url('/home'), $response->headers->get('Location'));

        $home = $this->get('/home');
        $home->assertStatus(302);
        $this->assertSame(route('employee.dashboard'), $home->headers->get('Location'));
    }

    public function test_manager_position_gets_manager_access_even_if_role_column_is_employee(): void
    {
        $this->seed(DatabaseSeeder::class);

        $manager = User::where('email', 'manager@test.com')->firstOrFail();
        $manager->update(['role' => 'employee']);

        $this->actingAs($manager->fresh())
            ->get(route('home'))
            ->assertRedirect(route('manager.dashboard'));

        $this->actingAs($manager->fresh())
            ->get(route('manager.dashboard'))
            ->assertOk();
    }

    public function test_hr_department_gets_hr_access_even_if_role_column_is_employee(): void
    {
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@company.com')->firstOrFail();
        $hr->update(['role' => 'employee']);

        $this->actingAs($hr->fresh())
            ->get(route('home'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($hr->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_pending_user_can_only_view_profile_until_hr_approval(): void
    {
        $pending = User::create([
            'name' => 'Pending Employee',
            'email' => 'pending@test.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'status' => 'pending',
        ]);

        $this->actingAs($pending)
            ->get(route('home'))
            ->assertRedirect(route('employee.profile'));

        $this->actingAs($pending)
            ->get(route('employee.profile'))
            ->assertOk()
            ->assertSee('not approved yet');

        $this->actingAs($pending)
            ->get(route('employee.dashboard'))
            ->assertRedirect(route('employee.profile'));
    }
}
