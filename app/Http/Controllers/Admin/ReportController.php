<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return app(HrController::class)->reports($request);
    }

    public function export(Request $request)
    {
        return app(HrController::class)->export($request);
    }

    public function calendar(Request $request): View
    {
        return app(HrController::class)->calendar($request);
    }
}
