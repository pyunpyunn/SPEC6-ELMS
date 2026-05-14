<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_role_dashboards_render(): void
    {
        $this->seed(DatabaseSeeder::class);

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
