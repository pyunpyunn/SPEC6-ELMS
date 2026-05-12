<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Employee;

class UserObserver
{
    public function created(User $user)
    {
        // Only create employee profile for users with 'employee' role
        if ($user->role === 'employee') {
            Employee::firstOrCreate([
                'user_id' => $user->id
            ], [
                'department' => $user->department ?? 'Unassigned',
                'position' => 'Employee',
                'date_hired' => now(),
                'contact_info' => '',
            ]);
        }
    }
}
