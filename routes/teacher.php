<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\MarksController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Teacher\TimetableController;

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

// Timetable
Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');

// Profile + password
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
