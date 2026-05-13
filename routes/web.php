<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Hr\HrController;

use App\Http\Controllers\EmployeePortalController;


Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return auth()->user()->role === 'hr_admin'
            ? redirect()->route('hr.dashboard')
            : redirect()->route('dashboard.employee');
    })->name('home');

    Route::middleware('role:hr_admin')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [HrController::class, 'dashboard'])->name('dashboard');
        Route::get('/users/pending', [HrController::class, 'pendingUsers'])->name('users.pending');
        Route::get('/users', [HrController::class, 'users'])->name('users.index');
        Route::post('/users/{user}/activate', [HrController::class, 'activateUser'])->name('users.activate');
        Route::patch('/users/{user}/deactivate', [HrController::class, 'deactivateUser'])->name('users.deactivate');

        Route::get('/employees', [HrController::class, 'employees'])->name('employees.index');
        Route::get('/employees/{employee}', [HrController::class, 'showEmployee'])->name('employees.show');
        Route::post('/employees', [HrController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}', [HrController::class, 'updateEmployee'])->name('employees.update');
        Route::patch('/employees/{employee}/deactivate', [HrController::class, 'deactivateEmployee'])->name('employees.deactivate');

        Route::get('/departments', [HrController::class, 'departments'])->name('departments.index');
        Route::post('/departments', [HrController::class, 'storeDepartment'])->name('departments.store');
        Route::put('/departments/{department}', [HrController::class, 'updateDepartment'])->name('departments.update');

        Route::get('/leave-types', [HrController::class, 'leaveTypes'])->name('leave-types.index');
        Route::post('/leave-types', [HrController::class, 'storeLeaveType'])->name('leave-types.store');
        Route::put('/leave-types/{leaveType}', [HrController::class, 'updateLeaveType'])->name('leave-types.update');
        Route::get('/my-leave', [HrController::class, 'myLeave'])->name('my-leave');
        Route::post('/my-leave', [HrController::class, 'storeMyLeave'])->name('my-leave.store');

        Route::get('/requests', [HrController::class, 'requests'])->name('requests.index');
        Route::patch('/requests/{leaveApplication}/review', [HrController::class, 'reviewRequest'])->name('requests.review');

        Route::get('/reports', [HrController::class, 'reports'])->name('reports.index');
        Route::get('/reports/export', [HrController::class, 'export'])->name('reports.export');
        Route::get('/calendar', [HrController::class, 'calendar'])->name('calendar');
    });

    Route::get('/notifications', [HrController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/{notification}/read', [HrController::class, 'readNotification'])->name('notifications.read');
    Route::get('/profile', [HrController::class, 'profile'])->name('profile');
    Route::put('/profile', [HrController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [HrController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('role:employee,manager')->get('/dashboard/employee', [EmployeePortalController::class, 'dashboard'])->name('dashboard.employee');
    Route::middleware('role:employee,manager')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/leave', [EmployeePortalController::class, 'myLeave'])->name('leave.index');
        Route::get('/leave/create', [EmployeePortalController::class, 'createLeave'])->name('leave.create');
        Route::post('/leave', [EmployeePortalController::class, 'storeLeave'])->name('leave.store');
        Route::get('/leave/history', [EmployeePortalController::class, 'leaveHistory'])->name('leave.history');
        Route::delete('/leave/{leaveApplication}', [EmployeePortalController::class, 'cancelLeave'])->name('leave.cancel');
        Route::get('/reports', [EmployeePortalController::class, 'reports'])->name('reports');
        Route::get('/leave/balances', [EmployeePortalController::class, 'reports'])->name('leave.balances');
        Route::get('/notifications', [EmployeePortalController::class, 'notifications'])->name('notifications');
        Route::get('/profile', [EmployeePortalController::class, 'profile'])->name('profile');
    });

});
