<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Receptionist\DashboardController;
use App\Http\Controllers\Receptionist\VisitorController;
use App\Http\Controllers\Receptionist\StudentEnquiryController;
use App\Http\Controllers\Receptionist\StudentRegistrationController;
use App\Http\Controllers\Receptionist\AdmissionController;
use App\Http\Controllers\Receptionist\VehicleController;
use App\Http\Controllers\Receptionist\RouteController;
use App\Http\Controllers\Receptionist\BusStopController;
use App\Http\Controllers\Receptionist\StudentTransportAssignmentController;
use App\Http\Controllers\Receptionist\TransportAttendanceController;
use App\Http\Controllers\Receptionist\HostelController;
use App\Http\Controllers\Receptionist\HostelFloorController;
use App\Http\Controllers\Receptionist\HostelRoomController;
use App\Http\Controllers\Receptionist\HostelBedAssignmentController;
use App\Http\Controllers\Receptionist\HostelAttendanceController;
use App\Http\Controllers\Receptionist\StaffController;


/*
|--------------------------------------------------------------------------
| Receptionist Routes
|--------------------------------------------------------------------------
|
| Routes for receptionist role users
|
|*/

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Visitor Management
Route::match(['get', 'post'], 'visitors', [VisitorController::class, 'index'])->name('visitors.index');
Route::resource('visitors', VisitorController::class)->except(['index']);
Route::post('visitors/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('visitors.check-in');
Route::post('visitors/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('visitors.check-out');
Route::get('visitors-export', [VisitorController::class, 'export'])->name('visitors.export');

// Student Enquiry Management
Route::match(['get', 'post'], 'student-enquiries', [StudentEnquiryController::class, 'index'])->name('student-enquiries.index');
Route::resource('student-enquiries', StudentEnquiryController::class)->except(['index']);

// Student Registration Management
Route::match(['get', 'post'], 'student-registrations', [StudentRegistrationController::class, 'index'])->name('student-registrations.index');
Route::get('student-registrations/enquiry/{id}', [StudentRegistrationController::class, 'getEnquiryData'])->name('student-registrations.enquiry-data');
Route::get('student-registrations/registration-fee/{classId}', [StudentRegistrationController::class, 'getRegistrationFee'])->name('student-registrations.registration-fee');
Route::get('student-registrations/download-template', [StudentRegistrationController::class, 'downloadTemplate'])->name('registrations.download-template');
Route::post('student-registrations/import', [StudentRegistrationController::class, 'import'])->name('registrations.import');
Route::get('student-registrations/{id}/pdf', [StudentRegistrationController::class, 'downloadPdf'])->name('student-registrations.pdf');
Route::resource('student-registrations', StudentRegistrationController::class)->except(['index']);

// Student Admission
Route::match(['get', 'post'], 'admission', [AdmissionController::class, 'index'])->name('admission.index');
Route::get('admission/class-data/{classId}', [AdmissionController::class, 'getClassData'])->name('admission.class-data');
Route::get('admission/registration/{id}', [AdmissionController::class, 'getRegistrationData'])->name('admission.getRegistrationData');
Route::get('admission/{id}/pdf', [AdmissionController::class, 'downloadPdf'])->name('admission.pdf');
Route::resource('admission', AdmissionController::class)->parameters([
    'admission' => 'student'
])->except(['index']);

// Vehicle Management
Route::match(['get', 'post'], 'vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::resource('vehicles', VehicleController::class)->except(['index']);
Route::get('vehicles-export', [VehicleController::class, 'export'])->name('vehicles.export');

// Route Management
Route::match(['get', 'post'], 'routes', [RouteController::class, 'index'])->name('routes.index');
Route::get('routes/vehicles', [RouteController::class, 'getVehicles'])->name('routes.vehicles');
Route::resource('routes', RouteController::class)->except(['index']);
Route::get('routes-export', [RouteController::class, 'export'])->name('routes.export');

// Bus Stop Management
Route::match(['get', 'post'], 'bus-stops', [BusStopController::class, 'index'])->name('bus-stops.index');
Route::resource('bus-stops', BusStopController::class)->except(['index']);
Route::get('bus-stops-export', [BusStopController::class, 'export'])->name('bus-stops.export');

// Transport Assignment Management
Route::resource('transport-assignments', StudentTransportAssignmentController::class);
Route::get('transport-assign-history', [StudentTransportAssignmentController::class, 'history'])->name('transport-assign-history.index');

// Transport Attendance Management
Route::get('transport-attendance', [TransportAttendanceController::class, 'index'])->name('transport-attendance.index');
Route::post('transport-attendance/get-routes', [TransportAttendanceController::class, 'getRoutes'])->name('transport-attendance.get-routes');
Route::post('transport-attendance/get-students', [TransportAttendanceController::class, 'getStudents'])->name('transport-attendance.get-students');
Route::post('transport-attendance', [TransportAttendanceController::class, 'store'])->name('transport-attendance.store');
Route::get('transport-attendance/month-wise-report', [TransportAttendanceController::class, 'monthWiseReport'])->name('transport-attendance.month-wise-report');
Route::post('transport-attendance/get-routes-for-report', [TransportAttendanceController::class, 'getRoutesForReport'])->name('transport-attendance.get-routes-for-report');

// Hostel Management
Route::resource('hostels', HostelController::class);
Route::get('hostels-export', [HostelController::class, 'export'])->name('hostels.export');
Route::resource('hostel-floors', HostelFloorController::class);
Route::get('hostel-floors-export', [HostelFloorController::class, 'export'])->name('hostel-floors.export');
Route::resource('hostel-rooms', HostelRoomController::class);
Route::get('hostel-rooms-export', [HostelRoomController::class, 'export'])->name('hostel-rooms.export');
Route::post('hostel-rooms/get-floors', [HostelRoomController::class, 'getFloors'])->name('hostel-rooms.get-floors');
// Specific routes must come BEFORE resource route to avoid conflicts
Route::get('hostel-bed-assignments/get-months', [HostelBedAssignmentController::class, 'getMonths'])->name('hostel-bed-assignments.get-months');
Route::post('hostel-bed-assignments/search-students', [HostelBedAssignmentController::class, 'searchStudents'])->name('hostel-bed-assignments.search-students');
Route::post('hostel-bed-assignments/get-floors', [HostelBedAssignmentController::class, 'getFloors'])->name('hostel-bed-assignments.get-floors');
Route::post('hostel-bed-assignments/get-rooms', [HostelBedAssignmentController::class, 'getRooms'])->name('hostel-bed-assignments.get-rooms');
Route::get('hostel-bed-assignments-export', [HostelBedAssignmentController::class, 'export'])->name('hostel-bed-assignments.export');
Route::resource('hostel-bed-assignments', HostelBedAssignmentController::class);

// Hostel Attendance
Route::post('hostel-attendance/get-students', [HostelAttendanceController::class, 'getStudents'])->name('hostel-attendance.get-students');
Route::get('hostel-attendance/report', [HostelAttendanceController::class, 'report'])->name('hostel-attendance.report');
Route::resource('hostel-attendance', HostelAttendanceController::class)->only(['index', 'store']);

// Staff Management
Route::get('staff/get-sections/{classId}', [StaffController::class, 'getSections'])->name('staff.get-sections');
Route::resource('staff', StaffController::class);

