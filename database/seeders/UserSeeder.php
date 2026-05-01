<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hr@test.com'],
            [
                'name' => 'HR Admin',
                'password' => bcrypt('password'),
                'role' => 'hr_admin'
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'Manager',
                'password' => bcrypt('password'),
                'role' => 'manager'
            ]
        );

        User::updateOrCreate(
            ['email' => 'emp@test.com'],
            [
                'name' => 'Employee',
                'password' => bcrypt('password'),
                'role' => 'employee'
            ]
        );
    }
}
