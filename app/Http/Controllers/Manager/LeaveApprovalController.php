<?php

namespace App\Http\Controllers\Manager;

use App\Http\Requests\Manager\LeaveDecisionRequest;
use App\Models\LeaveApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveApprovalController extends ManagerController
{
    public function index(Request $request): View
    {
        return $this->requests($request);
    }

    public function show(Request $request, LeaveApplication $leaveApplication): View
    {
        return $this->showRequest($request, $leaveApplication);
    }

    public function approve(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        return $this->reviewRequest($request, $leaveApplication);
    }

    public function reject(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        return $this->reviewRequest($request, $leaveApplication);
    }

    public function review(LeaveDecisionRequest $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        return $this->reviewRequest($request, $leaveApplication);
    }
}
