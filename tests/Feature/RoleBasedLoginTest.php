<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_login_with_employee_id_and_is_sent_to_admin_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'HR-0001',
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
            'email' => 'EMP-STAFF-01',
            'password' => 'staffpassword123',
        ]);

        $response->assertStatus(302);
        $this->assertSame(url('/home'), $response->headers->get('Location'));

        $home = $this->get('/home');
        $home->assertStatus(302);
        $this->assertSame(route('employee.dashboard'), $home->headers->get('Location'));
    }
}
