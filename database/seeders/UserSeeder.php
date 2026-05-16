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
        User::updateOrCreate(
            ['email' => 'hr@company.com'],
            [
                'name' => 'Kathleen Barro',
                'password' => Hash::make('password'),
                'role' => 'hr_admin',
                'status' => 'active',
            ]
        );
    }
}
