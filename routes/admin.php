<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;

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
Route::post('schools/{id}/restore', [SchoolController::class, 'restore'])->name('schools.restore');
Route::delete('schools/{id}/force-delete', [SchoolController::class, 'forceDelete'])->name('schools.force-delete');
Route::get('schools/{id}/features', [SchoolController::class, 'features'])->name('schools.features');
Route::put('schools/{id}/features', [SchoolController::class, 'updateFeatures'])->name('schools.update-features');
Route::post('schools/data', [SchoolController::class, 'index'])->name('schools.data');
Route::resource('schools', SchoolController::class);

// Global User Management
Route::match(['get', 'post'], '/users', [UserController::class, 'index'])->name('users.index');

// Audit Logs
Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

// Settings
Route::get('/change-password', [SettingController::class, 'changePassword'])->name('change-password');
Route::post('/change-password', [SettingController::class, 'updatePassword'])->name('update-password');
Route::get('/profile', [SettingController::class, 'profile'])->name('profile');
Route::put('/profile', [SettingController::class, 'updateProfile'])->name('update-profile');
Route::get('/settings/system', [SettingController::class, 'systemSettings'])->name('settings.system');
Route::post('/settings/system', [SettingController::class, 'updateSystemSettings'])->name('settings.update-system');

// Global Search
Route::get('/global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global-search');


