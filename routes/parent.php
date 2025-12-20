<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parent\DashboardController;
use App\Http\Controllers\Parent\StudentController;
use App\Http\Controllers\Parent\FeeController;
use App\Http\Controllers\Parent\AttendanceController;
use App\Http\Controllers\Parent\ResultController;

/*
|--------------------------------------------------------------------------
| Parent Routes
|--------------------------------------------------------------------------
|
| Routes for parents
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Children Management
Route::get('/children', [StudentController::class, 'index'])->name('children.index');
Route::get('/children/{student}', [StudentController::class, 'show'])->name('children.show');

// Fees
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

// Results
Route::get('/results', [ResultController::class, 'index'])->name('results.index');

// Other parent routes...

