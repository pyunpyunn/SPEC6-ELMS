<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['HR Administrator', 'hr@company.com', 'password', 'hr_admin'],
            ['IT Manager', 'manager@test.com', 'password', 'manager'],
            ['Finance Manager', 'elena.garcia@company.com', 'password', 'manager'],
            ['Staff Employee', 'staff@test.com', 'staffpassword123', 'employee'],
            ['Female Staff', 'female.staff@test.com', 'password', 'employee'],
        ])->each(fn (array $user) => User::updateOrCreate(
            ['email' => $user[1]],
            [
                'name' => $user[0],
                'password' => Hash::make($user[2]),
                'role' => $user[3],
                'status' => 'active',
            ]
        ));
    }
}
