<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['Kathleen Barro', 'hr@company.com', 'hr_admin'],
            ['Demo Manager', 'manager@company.com', 'manager'],
            ['Demo Employee', 'employee@company.com', 'employee'],
        ])->each(function (array $user): void {
            User::updateOrCreate(
                ['email' => $user[1]],
                [
                    'name' => $user[0],
                    'password' => Hash::make('password'),
                    'role' => $user[2],
                    'status' => 'active',
                ]
            );
        });
    }
}
