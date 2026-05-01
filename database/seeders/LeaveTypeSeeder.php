<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveType::create([
        'name' => 'Sick Leave',
        'annual_allocation' => 15,
        'requires_approval' => true
    ]);

    LeaveType::create([
        'name' => 'Vacation Leave',
        'annual_allocation' => 12,
        'requires_approval' => true
    ]);
    }
}
