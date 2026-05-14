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
        // Create only the default HR account
        User::updateOrCreate(
            ['email' => 'hr@company.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
                'role' => 'hr_admin',
                'status' => 'active',
            ]
        );
    }
}
