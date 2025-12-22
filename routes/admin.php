<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Admin Routes (Super Admin)
|--------------------------------------------------------------------------
|
| Routes for super admin to manage schools and platform
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// School Management
Route::resource('schools', SchoolController::class);


Route::get('/change-password', [SettingController::class, 'changePassword'])->name('change-password');
Route::get('/profile', [SettingController::class, 'profile'])->name('profile');

