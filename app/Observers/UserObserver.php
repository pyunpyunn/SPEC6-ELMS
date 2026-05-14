<?php

namespace App\Observers;

use App\Models\User;
class UserObserver
{
    public function created(User $user)
    {
        // HR activation owns employee profile creation so pending registrations stay validation-only.
    }
}
