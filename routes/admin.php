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

// Settings Routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/logo', [SettingController::class, 'logo'])->name('logo');
    Route::post('/logo/update', [SettingController::class, 'updateLogo'])->name('logo.update');
    Route::delete('/logo/{school}', [SettingController::class, 'deleteLogo'])->name('logo.delete');
    Route::get('/basic-info', [SettingController::class, 'basicInfo'])->name('basic-info');
    Route::get('/registration-fee', [SettingController::class, 'registrationFee'])->name('registration-fee');
    Route::get('/admission-fee', [SettingController::class, 'admissionFee'])->name('admission-fee');
    Route::get('/receipt-note', [SettingController::class, 'receiptNote'])->name('receipt-note');
    Route::get('/set-session', [SettingController::class, 'setSession'])->name('set-session');
    Route::get('/late-return-fine', [SettingController::class, 'lateReturnFine'])->name('late-return-fine');
    Route::get('/admission-fee-applicable', [SettingController::class, 'admissionFeeApplicable'])->name('admission-fee-applicable');
});

// Other routes
Route::get('/admission-news', [SettingController::class, 'admissionNews'])->name('admission-news');
Route::get('/support', [SettingController::class, 'support'])->name('support');
Route::get('/change-password', [SettingController::class, 'changePassword'])->name('change-password');
Route::get('/profile', [SettingController::class, 'profile'])->name('profile');

