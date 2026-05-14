<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\EmployeePortalController;
use Illuminate\View\View;

class DashboardController extends EmployeePortalController
{
    public function index(): View
    {
        return $this->dashboard();
    }
}
