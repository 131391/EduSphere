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

Route::middleware(['auth', 'role:receptionist'])->prefix('receptionist')->name('receptionist.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Visitor Management
    Route::resource('visitors', VisitorController::class);
    Route::post('visitors/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('visitors.check-in');
    Route::post('visitors/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('visitors.check-out');
    Route::get('visitors-export', [VisitorController::class, 'export'])->name('visitors.export');
});
