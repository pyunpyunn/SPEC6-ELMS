<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\LeaveTypeController as AdminLeaveTypeController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveApplicationController;
use App\Http\Controllers\Hr\HrController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LeaveApprovalController;
use App\Http\Controllers\Manager\ManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('welcome');
});

Route::middleware('auth')->get('/home', function () {
    return match (auth()->user()->role) {
        'hr_admin' => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default => redirect()->route('employee.dashboard'),
    };
})->name('home');

Route::middleware('auth')->get('/positions-by-department/{department_id}', function (int $department_id) {
        $positions = \App\Models\Position::where('department_id', $department_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($positions);
})->name('positions.by-department');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::delete('/leave/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])
        ->middleware('role:employee')
        ->name('leave.destroy');

    Route::middleware('role:hr_admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('/users/pending', [AdminUserController::class, 'pending'])->name('users.pending');
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
            Route::patch('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');

            Route::resource('employees', AdminEmployeeController::class);

            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');

            Route::resource('leave-types', AdminLeaveTypeController::class)
                ->parameters(['leave-types' => 'leaveType'])
                ->except(['show']);

            Route::get('/requests', [AdminLeaveRequestController::class, 'index'])->name('requests.index');
            Route::patch('/requests/{leaveApplication}/review', [AdminLeaveRequestController::class, 'review'])->name('requests.review');

            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/calendar', [AdminReportController::class, 'calendar'])->name('reports.calendar');
            Route::get('/calendar', [AdminReportController::class, 'calendar'])->name('calendar');

            Route::get('/my-leave', [AdminProfileController::class, 'myLeave'])->name('my-leave');
            Route::post('/my-leave', [AdminProfileController::class, 'storeMyLeave'])->name('my-leave.store');

            Route::get('/notifications', [AdminProfileController::class, 'notifications'])->name('notifications');
            Route::get('/notifications/{notification}/read', [AdminProfileController::class, 'readNotification'])->name('notifications.read');
            Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
            Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [AdminProfileController::class, 'password'])->name('profile.password');
        });

    Route::middleware('role:manager')
        ->prefix('manager')
        ->name('manager.')
        ->group(function () {
            Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

            Route::get('/approvals', [LeaveApprovalController::class, 'index'])->name('approvals.index');
            Route::get('/approvals/{leaveApplication}', [LeaveApprovalController::class, 'show'])->name('approvals.show');
            Route::patch('/approvals/{leaveApplication}/approve', [LeaveApprovalController::class, 'approve'])->name('approvals.approve');
            Route::patch('/approvals/{leaveApplication}/reject', [LeaveApprovalController::class, 'reject'])->name('approvals.reject');
            Route::patch('/approvals/{leaveApplication}/review', [LeaveApprovalController::class, 'review'])->name('approvals.review');

            Route::get('/calendar', [ManagerController::class, 'calendar'])->name('calendar');
            Route::get('/team', [ManagerController::class, 'team'])->name('team');
            Route::get('/my-leave', [ManagerController::class, 'myLeave'])->name('my-leave');
            Route::post('/my-leave', [ManagerController::class, 'storeMyLeave'])->name('my-leave.store');
            Route::get('/notifications', [ManagerController::class, 'notifications'])->name('notifications');
            Route::get('/notifications/{notification}/read', [ManagerController::class, 'readNotification'])->name('notifications.read');
            Route::get('/profile', [ManagerController::class, 'profile'])->name('profile');
            Route::put('/profile', [ManagerController::class, 'updateProfile'])->name('profile.update');
            Route::put('/profile/password', [ManagerController::class, 'updatePassword'])->name('profile.password');
        });

    Route::middleware('role:employee')
        ->prefix('employee')
        ->name('employee.')
        ->group(function () {
            Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
            Route::resource('leaves', LeaveApplicationController::class)
                ->parameters(['leaves' => 'leaveApplication'])
                ->only(['index', 'create', 'store', 'show']);
            Route::patch('/leaves/{leaveApplication}/cancel', [LeaveApplicationController::class, 'cancel'])->name('leaves.cancel');
            Route::delete('/leaves/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])->name('leaves.destroy');
            Route::get('/reports', [LeaveApplicationController::class, 'reports'])->name('reports');
            Route::get('/leave-balances', [LeaveApplicationController::class, 'reports'])->name('leave-balances');
            Route::get('/notifications', [LeaveApplicationController::class, 'notifications'])->name('notifications');
            Route::get('/notifications/{notification}/read', [LeaveApplicationController::class, 'readNotification'])->name('notifications.read');
            Route::get('/profile', [LeaveApplicationController::class, 'profile'])->name('profile');
        });
});
