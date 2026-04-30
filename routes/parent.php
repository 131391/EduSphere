<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parent\DashboardController;
use App\Http\Controllers\Parent\StudentController;
use App\Http\Controllers\Parent\FeeController;
use App\Http\Controllers\Parent\AttendanceController;
use App\Http\Controllers\Parent\ProfileController;
use App\Http\Controllers\Parent\ResultController;
use App\Http\Controllers\Parent\PaymentController;

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
Route::get('/fees/export', [FeeController::class, 'export'])->name('fees.export');
Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
Route::get('/fees/receipt/{receiptNo}', [FeeController::class, 'receipt'])->name('fees.receipt');

// Payments
Route::post('/payments/initiate/{fee_id}', [PaymentController::class, 'initiate'])->name('payments.initiate');
Route::post('/payments/verify', [PaymentController::class, 'verify'])->name('payments.verify');

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

// Results
Route::get('/results', [ResultController::class, 'index'])->name('results.index');

// Profile
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
