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
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

Route::middleware('auth')->get('/home', function () {
    $user = auth()->user();

    if ($user->status !== 'active') {
        return redirect()
            ->route('employee.profile')
            ->with('warning', 'Your account is not approved yet. HR must activate your account before you can use ELMS modules.');
    }

    return match ($user->getAccessLevel()) {
        'hr' => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default => redirect()->route('employee.dashboard'),
    };
})->name('home');

Route::middleware(['auth', 'account.approved'])->get('/positions-by-department/{department_id}', function (int $department_id) {
        $positions = \App\Models\Position::where('department_id', $department_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($positions);
})->name('positions.by-department');

Route::middleware(['auth', 'account.approved'])->get('/notifications/feed', function (Request $request) {
    $notifications = $request->user()
        ->notifications()
        ->latest()
        ->take(5)
        ->get()
        ->map(fn (SystemNotification $notification) => [
            'id' => $notification->id,
            'title' => $notification->title,
            'created_at' => $notification->created_at?->diffForHumans(),
            'unread' => $notification->read_at === null,
            'read_url' => route('notifications.read', $notification),
        ]);

    return response()->json([
        'unread_count' => $request->user()->notifications()->whereNull('read_at')->count(),
        'notifications' => $notifications,
    ]);
})->name('notifications.feed');

Route::middleware(['auth', 'account.approved'])->get('/notifications/{notification}/read', function (SystemNotification $notification) {
    abort_unless($notification->user_id === auth()->id(), 403);

    if (! $notification->read_at) {
        $notification->update(['read_at' => now()]);
    }

    $user = auth()->user();
    $fallback = match (true) {
        $user?->hasAccessRole('manager') && $notification->type === 'leave_request' => route('manager.approvals.index'),
        $user?->hasAccessRole('manager') && $notification->type === 'leave_status' => route('manager.my-leave'),
        default => $notification->action_url ?: route('home'),
    };

    return redirect($fallback);
})->name('notifications.read');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::delete('/leave/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])
        ->middleware(['account.approved', 'role:employee'])
        ->name('leave.destroy');

    Route::middleware(['account.approved', 'role:hr'])
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

            Route::get('/reports/yearly-compensation', [AdminReportController::class, 'yearlyCompensation'])->name('reports.yearly-compensation');
            Route::get('/reports/individual-balance', [AdminReportController::class, 'individualBalance'])->name('reports.individual-balance');
            Route::get('/reports/yearly-compensation/export', [AdminReportController::class, 'exportYearlyCompensation'])->name('reports.yearly-compensation.export');
            Route::get('/reports/individual-balance/export', [AdminReportController::class, 'exportIndividualBalance'])->name('reports.individual-balance.export');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/calendar', [AdminReportController::class, 'calendar'])->name('reports.calendar');
            Route::get('/reports/{section?}', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/calendar', [AdminReportController::class, 'calendar'])->name('calendar');

            Route::get('/my-leave', [AdminProfileController::class, 'myLeave'])->name('my-leave');
            Route::post('/my-leave', [AdminProfileController::class, 'storeMyLeave'])->name('my-leave.store');

            Route::get('/notifications', [AdminProfileController::class, 'notifications'])->name('notifications');
            Route::get('/notifications/{notification}/read', [AdminProfileController::class, 'readNotification'])->name('notifications.read');
            Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
            Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [AdminProfileController::class, 'password'])->name('profile.password');
        });

    Route::middleware(['account.approved', 'role:hr'])
        ->prefix('reports')
        ->name('reports.')
        ->group(function () {
            Route::get('/yearly-compensation', [AdminReportController::class, 'yearlyCompensation'])->name('yearly-compensation');
            Route::get('/individual-balance', [AdminReportController::class, 'individualBalance'])->name('individual-balance');
            Route::get('/yearly-compensation/export', [AdminReportController::class, 'exportYearlyCompensation'])->name('yearly-compensation.export');
            Route::get('/individual-balance/export', [AdminReportController::class, 'exportIndividualBalance'])->name('individual-balance.export');
        });

    Route::middleware(['account.approved', 'role:manager'])
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
            Route::patch('/my-leave/{leaveApplication}/cancel', [ManagerController::class, 'cancelMyLeave'])->name('my-leave.cancel');
            Route::get('/notifications', [ManagerController::class, 'notifications'])->name('notifications');
            Route::get('/notifications/{notification}/read', [ManagerController::class, 'readNotification'])->name('notifications.read');
            Route::get('/profile', [ManagerController::class, 'profile'])->name('profile');
            Route::put('/profile', [ManagerController::class, 'updateProfile'])->name('profile.update');
            Route::put('/profile/password', [ManagerController::class, 'updatePassword'])->name('profile.password');
        });

    Route::prefix('employee')
        ->name('employee.')
        ->group(function () {
            Route::get('/profile', [LeaveApplicationController::class, 'profile'])->name('profile');
        });

    Route::middleware(['account.approved', 'role:employee'])
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
        });
});
