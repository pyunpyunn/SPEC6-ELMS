<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function landing(): RedirectResponse|View
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function redirectByRole(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->status !== 'active') {
            return redirect()
                ->route('employee.profile')
                ->with('warning', 'Your account is not approved yet. HR must activate your account before you can use ELMS modules.');
        }

        if ($user->getAccessLevel() === 'hr') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->getAccessLevel() === 'manager') {
            return redirect()->route('manager.dashboard');
        }

        return redirect()->route('employee.dashboard');
    }
}
