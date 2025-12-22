<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Receptionist\DashboardController;
use App\Http\Controllers\Receptionist\VisitorController;

/*
|--------------------------------------------------------------------------
| Receptionist Routes
|--------------------------------------------------------------------------
|
| Routes for receptionist role users
|
*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Visitor Management
Route::resource('visitors', VisitorController::class);
Route::post('visitors/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('visitors.check-in');
Route::post('visitors/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('visitors.check-out');
Route::get('visitors-export', [VisitorController::class, 'export'])->name('visitors.export');

// Student Enquiry Management
Route::resource('student-enquiries', \App\Http\Controllers\Receptionist\StudentEnquiryController::class);

// Student Registration Management
Route::resource('student-registrations', \App\Http\Controllers\Receptionist\StudentRegistrationController::class);
