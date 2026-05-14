<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Http\Requests\Hr\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return app(HrController::class)->departments();
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        return app(HrController::class)->storeDepartment($request);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        return app(HrController::class)->updateDepartment($request, $department);
    }
}
