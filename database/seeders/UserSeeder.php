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
            ['Maria Andres', 'hr@company.com', 'hr_admin', 'active'],
            ['Roberto Cruz', 'manager@test.com', 'manager', 'active'],
            ['Elena Garcia', 'elena.garcia@company.com', 'manager', 'active'],
            ['Juan dela Cruz', 'juan.delacruz@company.com', 'employee', 'active'],
            ['Sofia Lim', 'sofia.lim@company.com', 'employee', 'active'],
            ['Renz Pascual', 'renz.pascual@company.com', 'employee', 'active'],
            ['Ana Reyes', 'ana.reyes@company.com', 'employee', 'pending', 'EMP-0052'],
            ['Miguel Torres', 'miguel.torres@company.com', 'employee', 'pending', 'EMP-0058'],
        ])->each(fn ($user) => User::updateOrCreate(
            ['email' => $user[1]],
            ['name' => $user[0], 'password' => Hash::make('password'), 'role' => $user[2], 'status' => $user[3], 'pending_employee_id' => $user[4] ?? null]
        ));
    }
}
