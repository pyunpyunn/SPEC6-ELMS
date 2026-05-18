<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Hr\HrController;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        return app(HrController::class)->employees($request);
    }

    public function create(Request $request): View
    {
        return $this->index($request);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        return app(HrController::class)->storeEmployee($request);
    }

    public function show(Employee $employee): View
    {
        return app(HrController::class)->showEmployee($employee);
    }

    public function edit(Employee $employee): View
    {
        return $this->show($employee);
    }

    public function update(StoreEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        return app(HrController::class)->updateEmployee($request, $employee);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->update(['employment_status' => 'terminated']);
        $employee->user?->update(['status' => 'inactive']);

        return redirect()->route('admin.employees.index')->with('warning', 'Employee deactivated.');
    }
}
