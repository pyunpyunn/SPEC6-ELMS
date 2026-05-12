<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeePortalController;

Route::get('/', function () {
    return view('welcome');
});

// A group for anyone who is logged in
Route::middleware(['auth'])->group(function () {
    
    // The shared home page you wanted
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/dashboard/employee', [EmployeePortalController::class, 'dashboard'])->name('dashboard.employee');
    Route::prefix('employee')->name('employee.')->group(function () {
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
