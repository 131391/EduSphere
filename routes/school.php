<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\School\DashboardController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\FeeController;
use App\Http\Controllers\School\ClassController;
use App\Http\Controllers\School\SectionController;
use App\Http\Controllers\School\AcademicYearController;
use App\Http\Controllers\School\StudentPromotionController;
use App\Http\Controllers\School\WaiverController;
use App\Http\Controllers\School\LateFeeController;
use App\Http\Controllers\School\FeeTypeController;
use App\Http\Controllers\School\MiscellaneousFeeController;
use App\Http\Controllers\School\FeeNameController;
use App\Http\Controllers\School\PaymentMethodController;
use App\Http\Controllers\School\SchoolBankController;
use App\Http\Controllers\School\AdmissionCodeController;
use App\Http\Controllers\School\StudentTypeController;
use App\Http\Controllers\School\BoardingTypeController;
use App\Http\Controllers\School\CorrespondingRelativeController;
use App\Http\Controllers\School\BloodGroupController;
use App\Http\Controllers\School\ReligionController;
use App\Http\Controllers\School\CategoryController;
use App\Http\Controllers\School\QualificationController;
use App\Http\Controllers\School\SchoolSettingsController;
use App\Http\Controllers\School\AdmissionNewsController;
use App\Http\Controllers\School\SupportController;
use App\Http\Controllers\School\RegistrationFeeController;
use App\Http\Controllers\School\FeeMasterController;
use App\Http\Controllers\School\SubjectController;
use App\Http\Controllers\School\Examination\SubjectController as ExamSubjectController;
use App\Http\Controllers\School\Examination\ExamTypeController;
use App\Http\Controllers\School\Examination\ExamController;
use App\Http\Controllers\School\Examination\GradeController;
use App\Http\Controllers\School\UserController;
use App\Http\Controllers\School\UserFavoriteController;
use App\Http\Controllers\School\AdmissionFeeController;
use App\Http\Controllers\School\RegistrationCodeController;
use App\Http\Controllers\School\FeePaymentController;
use App\Http\Controllers\School\LibraryController;
use App\Http\Controllers\School\AttendanceReportController;
use App\Http\Controllers\School\AdhocFeeController;
use App\Http\Controllers\School\FeeReportController;

/*
|--------------------------------------------------------------------------
| School Admin Routes
|--------------------------------------------------------------------------
|
| Routes for school administrators
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Registration routes redirect to student-registrations (kept for backward-compat nav links)
Route::get('/registrations', fn() => redirect()->route('school.student-registrations.index'))->name('registrations.index');

// Registration Code Management
Route::post('registration-codes/fetch', [RegistrationCodeController::class, 'index'])->name('registration-codes.fetch');
Route::resource('registration-codes', RegistrationCodeController::class)->only(['index', 'store', 'update', 'destroy']);

// Student Management
Route::post('fee-master/fetch', [FeeMasterController::class, 'index'])->name('fee-master.fetch');
Route::resource('fee-master', FeeMasterController::class);

// Favorites
Route::get('favorites', [UserFavoriteController::class, 'index'])->name('favorites.index');
Route::post('favorites/toggle', [UserFavoriteController::class, 'toggle'])->name('favorites.toggle');
Route::get('favorites/check', [UserFavoriteController::class, 'check'])->name('favorites.check');
Route::delete('favorites/{userFavorite}', [UserFavoriteController::class, 'destroy'])->name('favorites.destroy');

// Fee Management
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
Route::get('/ad-hoc-fees/create', [AdhocFeeController::class, 'create'])->name('ad-hoc-fees.create');
Route::post('/ad-hoc-fees', [AdhocFeeController::class, 'store'])->name('ad-hoc-fees.store');
Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
Route::get('/fee-management', [FeeController::class, 'index'])->name('fee-management');

// Fee Payments
Route::get('/fee-payments', [FeePaymentController::class, 'index'])->name('fee-payments.index');
Route::get('/fee-payments/collect/{student}', [FeePaymentController::class, 'collect'])->name('fee-payments.collect');
Route::post('/fee-payments/collect/{student}', [FeePaymentController::class, 'store'])->name('fee-payments.store');
Route::get('/fee-payments/receipt/{receipt_no}', [FeePaymentController::class, 'receipt'])->name('fee-payments.receipt');
Route::get('/fee-payments/receipt/{receipt_no}/pdf', [FeePaymentController::class, 'downloadPdf'])->name('fee-payments.receipt.pdf');
Route::delete('/fee-payments/{receipt_no}', [FeePaymentController::class, 'destroy'])->name('fee-payments.destroy');

// Waiver Management
Route::post('waivers/fetch', [WaiverController::class, 'index'])->name('waivers.fetch');
Route::resource('waivers', WaiverController::class);

// Late Fee Management
Route::post('late-fee/fetch', [LateFeeController::class, 'index'])->name('late-fee.fetch');
Route::resource('late-fee', LateFeeController::class);

// Class Management
Route::post('classes/fetch', [ClassController::class, 'index'])->name('classes.fetch');
Route::patch('classes/{class}/toggle-availability', [ClassController::class, 'toggleAvailability'])
    ->name('classes.toggle-availability');
Route::resource('classes', ClassController::class);

// Section Management
Route::post('sections/fetch', [SectionController::class, 'index'])->name('sections.fetch');
Route::resource('sections', SectionController::class);

// Academic Year Management
Route::post('academic-years/fetch', [AcademicYearController::class, 'index'])->name('academic-years.fetch');
Route::resource('academic-years', AcademicYearController::class);

// Student Promotion
Route::prefix('student-promotions')->name('student-promotions.')->group(function () {
    Route::get('/', [StudentPromotionController::class, 'index'])->name('index');
    Route::post('/preview', [StudentPromotionController::class, 'preview'])->name('preview');
    Route::post('/promote', [StudentPromotionController::class, 'promote'])->name('promote');
    Route::get('/history', [StudentPromotionController::class, 'history'])->name('history');
});

// Fee Type Management
Route::post('fee-types/fetch', [FeeTypeController::class, 'index'])->name('fee-types.fetch');
Route::resource('fee-types', FeeTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Miscellaneous Fee Management
Route::post('miscellaneous-fees/fetch', [MiscellaneousFeeController::class, 'index'])->name('miscellaneous-fees.fetch');
Route::resource('miscellaneous-fees', MiscellaneousFeeController::class)->only(['index', 'store', 'update', 'destroy']);

// Fee Name Management
Route::post('fee-names/fetch', [FeeNameController::class, 'index'])->name('fee-names.fetch');
Route::resource('fee-names', FeeNameController::class)->only(['index', 'store', 'update', 'destroy']);

// Payment Method Management
Route::post('payment-methods/fetch', [PaymentMethodController::class, 'index'])->name('payment-methods.fetch');
Route::resource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'update', 'destroy']);

// School Bank Management
Route::post('school-banks/fetch', [SchoolBankController::class, 'index'])->name('school-banks.fetch');
Route::resource('school-banks', SchoolBankController::class)->only(['index', 'store', 'update', 'destroy']);

// Admission Code Management
Route::post('admission-codes/fetch', [AdmissionCodeController::class, 'index'])->name('admission-codes.fetch');
Route::resource('admission-codes', AdmissionCodeController::class)->only(['index', 'store', 'update', 'destroy']);

// Student Type Management
Route::post('student-types/fetch', [StudentTypeController::class, 'index'])->name('student-types.fetch');
Route::resource('student-types', StudentTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Boarding Type Management
Route::post('boarding-types/fetch', [BoardingTypeController::class, 'index'])->name('boarding-types.fetch');
Route::resource('boarding-types', BoardingTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Corresponding Relative Management
Route::post('corresponding-relatives/fetch', [CorrespondingRelativeController::class, 'index'])->name('corresponding-relatives.fetch');
Route::resource('corresponding-relatives', CorrespondingRelativeController::class)->only(['index', 'store', 'update', 'destroy']);

// Blood Group Management
Route::post('blood-groups/fetch', [BloodGroupController::class, 'index'])->name('blood-groups.fetch');
Route::resource('blood-groups', BloodGroupController::class)->only(['index', 'store', 'update', 'destroy']);

// Religion Management
Route::post('religions/fetch', [ReligionController::class, 'index'])->name('religions.fetch');
Route::resource('religions', ReligionController::class)->only(['index', 'store', 'update', 'destroy']);

// Category Management
Route::post('categories/fetch', [CategoryController::class, 'index'])->name('categories.fetch');
Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

// Qualification Management
Route::post('qualifications/fetch', [QualificationController::class, 'index'])->name('qualifications.fetch');
Route::resource('qualifications', QualificationController::class)->only(['index', 'store', 'update', 'destroy']);

// School Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('basic-info', [SchoolSettingsController::class, 'basicInfo'])->name('basic-info');
    Route::put('basic-info', [SchoolSettingsController::class, 'updateBasicInfo'])->name('basic-info.update');
    
    Route::get('logo', [SchoolSettingsController::class, 'logo'])->name('logo');
    Route::put('logo', [SchoolSettingsController::class, 'updateLogo'])->name('logo.update');
    
    Route::get('site-icon', [SchoolSettingsController::class, 'siteIcon'])->name('site-icon');
    Route::put('site-icon', [SchoolSettingsController::class, 'updateSiteIcon'])->name('site-icon.update');
    
    Route::get('general', [SchoolSettingsController::class, 'generalSettings'])->name('general');
    Route::put('general', [SchoolSettingsController::class, 'updateGeneralSettings'])->name('general.update');
    
    Route::get('session', [SchoolSettingsController::class, 'session'])->name('session');
    Route::put('session', [SchoolSettingsController::class, 'updateSession'])->name('session.update');
    
    Route::get('receipt-note', [SchoolSettingsController::class, 'receiptNote'])->name('receipt-note');
    Route::put('receipt-note', [SchoolSettingsController::class, 'updateReceiptNote'])->name('receipt-note.update');

    Route::post('registration-fee/fetch', [RegistrationFeeController::class, 'index'])->name('registration-fee.fetch');
    Route::resource('registration-fee', RegistrationFeeController::class);

    Route::post('admission-fee/fetch', [AdmissionFeeController::class, 'index'])->name('admission-fee.fetch');
    Route::resource('admission-fee', AdmissionFeeController::class);
});

// Admission News
Route::post('admission-news/fetch', [AdmissionNewsController::class, 'index'])->name('admission-news.fetch');
Route::resource('admission-news', AdmissionNewsController::class)->only(['index', 'store', 'update', 'destroy']);

// Support
Route::get('support', [SupportController::class, 'index'])->name('support');

// Examination Module
Route::prefix('examination')->name('examination.')->group(function () {
    Route::post('subjects/fetch', [ExamSubjectController::class, 'index'])->name('subjects.fetch');
    Route::get('subjects', [ExamSubjectController::class, 'index'])->name('subjects.index');
    Route::post('subjects', [ExamSubjectController::class, 'store'])->name('subjects.store');
    Route::delete('subjects/{id}', [ExamSubjectController::class, 'destroy'])->name('subjects.destroy');
    
    Route::post('exam-types/fetch', [ExamTypeController::class, 'index'])->name('exam-types.fetch');
    Route::resource('exam-types', ExamTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    
    Route::post('exams/fetch', [ExamController::class, 'index'])->name('exams.fetch');
    Route::resource('exams', ExamController::class)->only(['index', 'store', 'destroy']);

    Route::post('grades/fetch', [GradeController::class, 'index'])->name('grades.fetch');
    Route::resource('grades', GradeController::class)->only(['index', 'store', 'update', 'destroy']);

    // Mark Entry
    Route::get('marks', [ExamController::class, 'marksEntry'])->name('marks.index');
    Route::get('marks/enter', [ExamController::class, 'enterMarks'])->name('marks.entry');
    Route::post('marksGroup', [ExamController::class, 'storeMarks'])->name('marks.store');

    // Tabulation
    Route::get('exams/{exam}/tabulate', [ExamController::class, 'tabulate'])->name('exams.tabulate');
});

// Subject Management
Route::post('subjects/fetch', [SubjectController::class, 'index'])->name('subjects.fetch');
Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);

// User Management
Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('users/fetch', [UserController::class, 'index'])->name('users.fetch');

// Student Enquiry Management
Route::get('student-enquiries', [\App\Http\Controllers\School\StudentEnquiryController::class, 'index'])->name('student-enquiries.index');
Route::post('student-enquiries/fetch', [\App\Http\Controllers\School\StudentEnquiryController::class, 'index'])->name('student-enquiries.fetch');
Route::patch('student-enquiries/{studentEnquiry}/status', [\App\Http\Controllers\School\StudentEnquiryController::class, 'updateStatus'])->name('student-enquiries.update-status');
Route::resource('student-enquiries', \App\Http\Controllers\School\StudentEnquiryController::class)->except(['index']);

// Student Registration Management
Route::get('student-registrations', [\App\Http\Controllers\School\StudentRegistrationController::class, 'index'])->name('student-registrations.index');
Route::post('student-registrations/fetch', [\App\Http\Controllers\School\StudentRegistrationController::class, 'index'])->name('student-registrations.fetch');
Route::get('student-registrations/enquiry/{id}', [\App\Http\Controllers\School\StudentRegistrationController::class, 'getEnquiryData'])->name('student-registrations.enquiry-data');
Route::get('student-registrations/registration-fee/{classId}', [\App\Http\Controllers\School\StudentRegistrationController::class, 'getRegistrationFee'])->name('student-registrations.registration-fee');
Route::get('student-registrations/download-template', [\App\Http\Controllers\School\StudentRegistrationController::class, 'downloadTemplate'])->name('registrations.download-template');
Route::post('student-registrations/import', [\App\Http\Controllers\School\StudentRegistrationController::class, 'import'])->name('registrations.import');
Route::get('student-registrations/{id}/pdf', [\App\Http\Controllers\School\StudentRegistrationController::class, 'downloadPdf'])->name('student-registrations.pdf');
Route::resource('student-registrations', \App\Http\Controllers\School\StudentRegistrationController::class)->except(['index']);

// Admission Management
Route::get('admission', [\App\Http\Controllers\School\AdmissionController::class, 'index'])->name('admission.index');
Route::post('admission/fetch', [\App\Http\Controllers\School\AdmissionController::class, 'index'])->name('admission.fetch');
Route::get('admission/registration/{id}', [\App\Http\Controllers\School\AdmissionController::class, 'getRegistrationData'])->name('admission.getRegistrationData');
Route::get('admission/{id}/pdf', [\App\Http\Controllers\School\AdmissionController::class, 'downloadPdf'])->name('admission.pdf');
Route::get('admission/class-data/{classId}', [\App\Http\Controllers\School\AdmissionController::class, 'getClassData'])->name('admission.class-data');
Route::resource('admission', \App\Http\Controllers\School\AdmissionController::class)->except(['index'])->parameters([
    'admission' => 'student'
]);

// Library Management
Route::prefix('library')->name('library.')->group(function () {
    Route::get('/', [LibraryController::class, 'index'])->name('index');
    Route::post('/books', [LibraryController::class, 'storeBook'])->name('books.store');
    Route::get('/issues', [LibraryController::class, 'issues'])->name('issues');
    Route::post('/issue', [LibraryController::class, 'issueBook'])->name('issue.store');
    Route::post('/return/{issue}', [LibraryController::class, 'returnBook'])->name('return');
});

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/monthly', [AttendanceReportController::class, 'monthly'])->name('monthly');
        Route::get('/student', [AttendanceReportController::class, 'student'])->name('student');
        Route::get('/daily', [AttendanceReportController::class, 'daily'])->name('daily');
    });

    Route::prefix('fees')->name('fees.')->group(function () {
        Route::get('/', [FeeReportController::class, 'index'])->name('index');
        Route::get('/daily-collection', [FeeReportController::class, 'dailyCollection'])->name('daily-collection');
        Route::get('/defaulters', [FeeReportController::class, 'defaulters'])->name('defaulters');
    });
});

// Facility Management
Route::prefix('facilities')->name('facilities.')->group(function () {
    Route::get('/', [\App\Http\Controllers\School\StudentFacilityController::class, 'index'])->name('index');
    Route::post('/transport/{student}', [\App\Http\Controllers\School\StudentTransportController::class, 'assignTransport'])->name('transport.assign');
    Route::post('/hostel/{student}', [\App\Http\Controllers\School\StudentFacilityController::class, 'assignHostel'])->name('hostel.assign');
});

// Transport Management
Route::prefix('transport')->name('transport.')->group(function () {
    Route::post('/vehicles/fetch', [\App\Http\Controllers\School\VehicleController::class, 'index'])->name('vehicles.fetch');
    Route::resource('vehicles', \App\Http\Controllers\School\VehicleController::class)->except(['create', 'edit', 'show']);

    Route::post('/routes/fetch', [\App\Http\Controllers\School\TransportRouteController::class, 'index'])->name('transport_routes.fetch');
    Route::resource('routes', \App\Http\Controllers\School\TransportRouteController::class)->names('transport_routes')->except(['create', 'edit', 'show']);

    Route::post('/bus-stops/fetch', [\App\Http\Controllers\School\BusStopController::class, 'index'])->name('bus_stops.fetch');
    Route::resource('bus-stops', \App\Http\Controllers\School\BusStopController::class)->names('bus_stops')->except(['create', 'edit', 'show']);

    Route::get('/attendance', [\App\Http\Controllers\School\TransportAttendanceController::class, 'index'])->name('transport_attendance.index');
    Route::post('/attendance', [\App\Http\Controllers\School\TransportAttendanceController::class, 'store'])->name('transport_attendance.store');
});

// Other school admin routes...

