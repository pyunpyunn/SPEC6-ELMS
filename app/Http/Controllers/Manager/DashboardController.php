<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends ManagerController
{
    public function index(Request $request): View
    {
        return $this->dashboard($request);
    }
}
