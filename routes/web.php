<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth')->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Super Admin routes (no tenant middleware)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin'])->group(function () {
    require __DIR__.'/admin.php';
});

// Location API Routes
Route::group(['prefix' => 'api/location'], function () {
    Route::get('countries', [App\Http\Controllers\LocationController::class, 'getCountries'])->name('api.location.countries');
    Route::get('states/{countryId}', [App\Http\Controllers\LocationController::class, 'getStates'])->name('api.location.states');
    Route::get('cities/{stateId}', [App\Http\Controllers\LocationController::class, 'getCities'])->name('api.location.cities');
});



// School Admin routes (with tenant middleware)
Route::prefix('school')->name('school.')->middleware(['auth', 'tenant', 'school.access', 'role:school_admin'])->group(function () {
    require __DIR__.'/school.php';
});

// Teacher routes (with tenant middleware)
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'tenant', 'school.access', 'role:teacher'])->group(function () {
    require __DIR__.'/teacher.php';
});

// Student routes (with tenant middleware)
Route::prefix('student')->name('student.')->middleware(['auth', 'tenant', 'school.access', 'role:student'])->group(function () {
    require __DIR__.'/student.php';
});

// Parent routes (with tenant middleware)
Route::prefix('parent')->name('parent.')->middleware(['auth', 'tenant', 'school.access', 'role:parent'])->group(function () {
    require __DIR__.'/parent.php';
});

// Receptionist routes (with tenant middleware)
Route::prefix('receptionist')->name('receptionist.')->middleware(['auth', 'tenant', 'school.access', 'role:receptionist'])->group(function () {
    require __DIR__.'/receptionist.php';
});

