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
Route::get('student-registrations/enquiry/{id}', [\App\Http\Controllers\Receptionist\StudentRegistrationController::class, 'getEnquiryData'])->name('student-registrations.enquiry-data');
Route::get('student-registrations/registration-fee/{classId}', [\App\Http\Controllers\Receptionist\StudentRegistrationController::class, 'getRegistrationFee'])->name('student-registrations.registration-fee');
Route::get('student-registrations/{id}/pdf', [\App\Http\Controllers\Receptionist\StudentRegistrationController::class, 'downloadPdf'])->name('student-registrations.pdf');
Route::resource('student-registrations', \App\Http\Controllers\Receptionist\StudentRegistrationController::class);

// Admission// Student Admission
Route::get('admission/class-data/{classId}', [\App\Http\Controllers\Receptionist\AdmissionController::class, 'getClassData'])->name('admission.class-data');
Route::get('admission/registration/{id}', [\App\Http\Controllers\Receptionist\AdmissionController::class, 'getRegistrationData'])->name('admission.getRegistrationData');
Route::get('admission/{id}/pdf', [\App\Http\Controllers\Receptionist\AdmissionController::class, 'downloadPdf'])->name('admission.pdf');
Route::resource('admission', \App\Http\Controllers\Receptionist\AdmissionController::class)->parameters([
    'admission' => 'student'
]);

// Vehicle Management
Route::resource('vehicles', \App\Http\Controllers\Receptionist\VehicleController::class);
Route::get('vehicles-export', [\App\Http\Controllers\Receptionist\VehicleController::class, 'export'])->name('vehicles.export');

// Route Management
Route::get('routes/vehicles', [\App\Http\Controllers\Receptionist\RouteController::class, 'getVehicles'])->name('routes.vehicles');
Route::resource('routes', \App\Http\Controllers\Receptionist\RouteController::class);
Route::get('routes-export', [\App\Http\Controllers\Receptionist\RouteController::class, 'export'])->name('routes.export');

// Bus Stop Management
Route::resource('bus-stops', \App\Http\Controllers\Receptionist\BusStopController::class);
Route::get('bus-stops-export', [\App\Http\Controllers\Receptionist\BusStopController::class, 'export'])->name('bus-stops.export');

// Transport Assignment Management
Route::resource('transport-assignments', \App\Http\Controllers\Receptionist\StudentTransportAssignmentController::class);
Route::get('transport-assign-history', [\App\Http\Controllers\Receptionist\StudentTransportAssignmentController::class, 'history'])->name('transport-assign-history.index');

// Transport Attendance Management
Route::get('transport-attendance', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'index'])->name('transport-attendance.index');
Route::post('transport-attendance/get-routes', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'getRoutes'])->name('transport-attendance.get-routes');
Route::post('transport-attendance/get-students', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'getStudents'])->name('transport-attendance.get-students');
Route::post('transport-attendance', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'store'])->name('transport-attendance.store');
Route::get('transport-attendance/month-wise-report', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'monthWiseReport'])->name('transport-attendance.month-wise-report');
Route::post('transport-attendance/get-routes-for-report', [\App\Http\Controllers\Receptionist\TransportAttendanceController::class, 'getRoutesForReport'])->name('transport-attendance.get-routes-for-report');

// Hostel Management
Route::resource('hostels', \App\Http\Controllers\Receptionist\HostelController::class);
Route::get('hostels-export', [\App\Http\Controllers\Receptionist\HostelController::class, 'export'])->name('hostels.export');
Route::resource('hostel-floors', \App\Http\Controllers\Receptionist\HostelFloorController::class);
Route::get('hostel-floors-export', [\App\Http\Controllers\Receptionist\HostelFloorController::class, 'export'])->name('hostel-floors.export');
Route::resource('hostel-rooms', \App\Http\Controllers\Receptionist\HostelRoomController::class);
Route::get('hostel-rooms-export', [\App\Http\Controllers\Receptionist\HostelRoomController::class, 'export'])->name('hostel-rooms.export');
Route::post('hostel-rooms/get-floors', [\App\Http\Controllers\Receptionist\HostelRoomController::class, 'getFloors'])->name('hostel-rooms.get-floors');
// Specific routes must come BEFORE resource route to avoid conflicts
Route::get('hostel-bed-assignments/get-months', [\App\Http\Controllers\Receptionist\HostelBedAssignmentController::class, 'getMonths'])->name('hostel-bed-assignments.get-months');
Route::post('hostel-bed-assignments/search-students', [\App\Http\Controllers\Receptionist\HostelBedAssignmentController::class, 'searchStudents'])->name('hostel-bed-assignments.search-students');
Route::post('hostel-bed-assignments/get-floors', [\App\Http\Controllers\Receptionist\HostelBedAssignmentController::class, 'getFloors'])->name('hostel-bed-assignments.get-floors');
Route::post('hostel-bed-assignments/get-rooms', [\App\Http\Controllers\Receptionist\HostelBedAssignmentController::class, 'getRooms'])->name('hostel-bed-assignments.get-rooms');
Route::get('hostel-bed-assignments-export', [\App\Http\Controllers\Receptionist\HostelBedAssignmentController::class, 'export'])->name('hostel-bed-assignments.export');
Route::resource('hostel-bed-assignments', \App\Http\Controllers\Receptionist\HostelBedAssignmentController::class);

// Hostel Attendance
Route::post('hostel-attendance/get-students', [\App\Http\Controllers\Receptionist\HostelAttendanceController::class, 'getStudents'])->name('hostel-attendance.get-students');
Route::get('hostel-attendance/report', [\App\Http\Controllers\Receptionist\HostelAttendanceController::class, 'report'])->name('hostel-attendance.report');
Route::resource('hostel-attendance', \App\Http\Controllers\Receptionist\HostelAttendanceController::class)->only(['index', 'store']);

// Staff Management
Route::get('staff/get-sections/{classId}', [\App\Http\Controllers\Receptionist\StaffController::class, 'getSections'])->name('staff.get-sections');
Route::resource('staff', \App\Http\Controllers\Receptionist\StaffController::class);

