<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\EmployeePortalController;
use App\Http\Requests\StoreLeaveApplicationRequest;
use App\Models\LeaveApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveApplicationController extends EmployeePortalController
{
    public function index(): View
    {
        return $this->myLeave();
    }

    public function create(): View
    {
        return view('employee.leaves.create', $this->portalData());
    }

    public function store(StoreLeaveApplicationRequest $request): RedirectResponse
    {
        return $this->storeLeave($request);
    }

    public function show(LeaveApplication $leaveApplication): View
    {
        $employee = Auth::user()?->employee;

        abort_unless($employee && $leaveApplication->employee_id === $employee->id, 403);

        return view('employee.leaves.show', [
            'employee' => $employee,
            'leave' => $leaveApplication->load(['leaveType', 'reviewer']),
        ]);
    }

    public function cancel(LeaveApplication $leaveApplication): RedirectResponse
    {
        return $this->cancelLeave($leaveApplication);
    }

    public function destroy(LeaveApplication $leaveApplication): RedirectResponse
    {
        return $this->cancelLeave($leaveApplication);
    }
}
