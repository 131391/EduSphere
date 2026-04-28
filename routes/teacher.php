<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\MarksController;
use App\Http\Controllers\Teacher\StudentController;

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
|
| Routes for teachers
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Attendance Management
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

// Student Management
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');

// Mark Entry (per-subject, scoped to teacher's assigned exam_subjects)
Route::get('/marks', [MarksController::class, 'index'])->name('marks.index');
Route::get('/marks/enter', [MarksController::class, 'entry'])->name('marks.entry');
Route::post('/marks', [MarksController::class, 'store'])->name('marks.store');

// Other teacher routes...
