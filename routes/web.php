<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController; // Make sure to import your controller!

Route::get('/', function () {
    return view('welcome');
});

// A group for anyone who is logged in
Route::middleware(['auth'])->group(function () {
    
    // The shared home page you wanted
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // This will work once you have the EmployeeController set up
    Route::resource('employees', EmployeeController::class);
});