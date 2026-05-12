<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveType;
use App\Models\LeaveApplication;

class DashboardController extends Controller
{
    public function employee()
    {
        return app(EmployeePortalController::class)->dashboard();
    }
}
