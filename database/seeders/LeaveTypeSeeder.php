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
        collect([
            ['Sick Leave', 15, true, false, 'Medical certificate required for 3 or more days.'],
            ['Maternity Leave', 105, true, true, '105 days; 120 days for solo parent; 60 days for stillbirth or miscarriage.'],
            ['Paternity Leave', 7, true, true, 'Paid leave for the first 4 deliveries or miscarriage.'],
            ['Bereavement Leave', 15, true, false, 'Also called RIP leave in the prototype notes.'],
            ['Vacation Leave', 15, true, false, 'Unused days are eligible for yearly compensation.'],
            ['Special Emergency Leave', 15, true, false, 'For urgent personal or family emergencies.'],
        ])->each(fn ($type) => LeaveType::updateOrCreate(
            ['name' => $type[0]],
            [
                'slug' => str($type[0])->slug(),
                'annual_allocation' => $type[1],
                'requires_approval' => $type[2],
                'requires_proof' => $type[3],
                'proof_rules' => $type[4],
                'is_active' => true,
            ]
        ));
    }
}
