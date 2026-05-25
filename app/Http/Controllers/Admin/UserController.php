<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Http\Requests\Hr\ActivateUserRequest;
use App\Http\Requests\Hr\DeleteUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        return app(HrController::class)->users($request);
    }

    public function pending(Request $request): View
    {
        return app(HrController::class)->pendingUsers($request);
    }

    public function activate(ActivateUserRequest $request, User $user): RedirectResponse
    {
        return app(HrController::class)->activateUser($request, $user);
    }

    public function deactivate(User $user): RedirectResponse
    {
        return app(HrController::class)->deactivateUser($user);
    }

    public function destroy(DeleteUserRequest $request, User $user): RedirectResponse
    {
        return app(HrController::class)->deleteUser($request, $user);
    }
}
