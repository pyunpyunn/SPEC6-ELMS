<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesElmsFixtures;
use Tests\TestCase;

class RolePageSmokeTest extends TestCase
{
    use CreatesElmsFixtures;
    use RefreshDatabase;

    public function test_main_role_dashboards_render(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->createEmployeeProfile('manager@test.com', 'IT-3000-001', 'IT', 'IT Manager', 'manager');
        $this->createEmployeeProfile('staff@test.com', 'IT-3002-001', 'IT', 'Developer');

        $this->actingAs(User::where('email', 'hr@company.com')->firstOrFail())
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs(User::where('email', 'manager@test.com')->firstOrFail())
            ->get(route('manager.dashboard'))
            ->assertOk();

        $this->actingAs(User::where('email', 'staff@test.com')->firstOrFail())
            ->get(route('employee.dashboard'))
            ->assertOk();
    }
}
