<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        return app(HrController::class)->leaveTypes();
    }

    public function create(): View
    {
        return $this->index();
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        return app(HrController::class)->storeLeaveType($request);
    }

    public function edit(LeaveType $leaveType): View
    {
        return $this->index();
    }

    public function update(StoreLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        return app(HrController::class)->updateLeaveType($request, $leaveType);
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update(['is_active' => false]);

        return back()->with('warning', 'Leave type deactivated.');
    }
}
