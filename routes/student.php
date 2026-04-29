<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\FeeController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\ResultController;
use App\Http\Controllers\Student\TimetableController;
use App\Http\Controllers\Student\LibraryController as StudentLibraryController;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
| Routes for students
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Fees
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

// Results
Route::get('/results', [ResultController::class, 'index'])->name('results.index');
Route::get('/results/{result}', [ResultController::class, 'show'])->name('results.show');

// Timetable
Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');

// Library — read-only "My borrowed books" view
Route::get('/library', [StudentLibraryController::class, 'index'])->name('library.index');

// Other student routes...

