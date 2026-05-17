<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Http\Requests\Hr\ProfileRequest;
use App\Http\Requests\Hr\StoreHrLeaveRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function myLeave(Request $request): View
    {
        return app(HrController::class)->myLeave($request);
    }

    public function storeMyLeave(StoreHrLeaveRequest $request): RedirectResponse
    {
        return app(HrController::class)->storeMyLeave($request);
    }

    public function notifications(): View
    {
        return app(HrController::class)->notifications();
    }

    public function readNotification(SystemNotification $notification): RedirectResponse
    {
        return app(HrController::class)->readNotification($notification);
    }

    public function show(): View
    {
        return app(HrController::class)->profile();
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        return app(HrController::class)->updateProfile($request);
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        return app(HrController::class)->updatePassword($request);
    }
}
