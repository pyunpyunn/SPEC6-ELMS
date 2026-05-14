<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Models\LeaveApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        return app(HrController::class)->requests($request);
    }

    public function review(Request $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        return app(HrController::class)->reviewRequest($request, $leaveApplication);
    }
}
