<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\School\DashboardController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\FeeController;
use App\Http\Controllers\School\RegistrationController;
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

/*
|--------------------------------------------------------------------------
| School Admin Routes
|--------------------------------------------------------------------------
|
| Routes for school administrators
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Registration Management
Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
Route::post('/registrations/import', [RegistrationController::class, 'import'])->name('registrations.import');
Route::get('/registrations/download-template', [RegistrationController::class, 'downloadTemplate'])->name('registrations.download-template');

// Registration Code Management
Route::resource('registration-codes', RegistrationCodeController::class)->only(['index', 'store', 'update', 'destroy']);

// Student Management
Route::resource('students', StudentController::class);
Route::resource('fee-master', FeeMasterController::class);

// Favorites
Route::get('favorites', [UserFavoriteController::class, 'index'])->name('favorites.index');
Route::post('favorites/toggle', [UserFavoriteController::class, 'toggle'])->name('favorites.toggle');
Route::get('favorites/check', [UserFavoriteController::class, 'check'])->name('favorites.check');
Route::delete('favorites/{userFavorite}', [UserFavoriteController::class, 'destroy'])->name('favorites.destroy');

// Fee Management
Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
Route::get('/fee-management', [FeeController::class, 'index'])->name('fee-management');

// Fee Payments
Route::get('/fee-payments', [FeePaymentController::class, 'index'])->name('fee-payments.index');
Route::get('/fee-payments/collect/{student}', [FeePaymentController::class, 'collect'])->name('fee-payments.collect');
Route::post('/fee-payments/collect/{student}', [FeePaymentController::class, 'store'])->name('fee-payments.store');
Route::get('/fee-payments/receipt/{receipt_no}', [FeePaymentController::class, 'receipt'])->name('fee-payments.receipt');

// Waiver Management
Route::get('/waivers', [WaiverController::class, 'index'])->name('waivers.index');
Route::get('/waivers/create', [WaiverController::class, 'create'])->name('waivers.create');
Route::post('/waivers', [WaiverController::class, 'store'])->name('waivers.store');

// Late Fee Management
Route::resource('late-fee', LateFeeController::class);

// Class Management
// Class Management
Route::patch('classes/{class}/toggle-availability', [ClassController::class, 'toggleAvailability'])
    ->name('classes.toggle-availability');
Route::resource('classes', ClassController::class);

// Section Management
Route::resource('sections', SectionController::class);

// Academic Year Management
Route::resource('academic-years', AcademicYearController::class);

// Student Promotion
Route::prefix('student-promotions')->name('student-promotions.')->group(function () {
    Route::get('/', [StudentPromotionController::class, 'index'])->name('index');
    Route::post('/preview', [StudentPromotionController::class, 'preview'])->name('preview');
    Route::post('/promote', [StudentPromotionController::class, 'promote'])->name('promote');
    Route::get('/history', [StudentPromotionController::class, 'history'])->name('history');
});

// Fee Type Management
Route::resource('fee-types', FeeTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Miscellaneous Fee Management
Route::resource('miscellaneous-fees', MiscellaneousFeeController::class)->only(['index', 'store', 'update', 'destroy']);

// Fee Name Management
Route::resource('fee-names', FeeNameController::class)->only(['index', 'store', 'update', 'destroy']);

// Payment Method Management
Route::resource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'update', 'destroy']);

// School Bank Management
Route::resource('school-banks', SchoolBankController::class)->only(['index', 'store', 'update', 'destroy']);

// Admission Code Management
Route::resource('admission-codes', AdmissionCodeController::class)->only(['index', 'store', 'update', 'destroy']);

// Student Type Management
Route::resource('student-types', StudentTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Boarding Type Management
Route::resource('boarding-types', BoardingTypeController::class)->only(['index', 'store', 'update', 'destroy']);

// Corresponding Relative Management
Route::resource('corresponding-relatives', CorrespondingRelativeController::class)->only(['index', 'store', 'update', 'destroy']);

// Blood Group Management
Route::resource('blood-groups', BloodGroupController::class)->only(['index', 'store', 'update', 'destroy']);

// Religion Management
Route::resource('religions', ReligionController::class)->only(['index', 'store', 'update', 'destroy']);

// Category Management
Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

// Qualification Management
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

    Route::resource('registration-fee', RegistrationFeeController::class);
    Route::resource('admission-fee', AdmissionFeeController::class);
});

// Admission News
Route::resource('admission-news', AdmissionNewsController::class)->only(['index', 'store', 'update', 'destroy']);

// Support
Route::get('support', [SupportController::class, 'index'])->name('support');

// Examination Module
Route::prefix('examination')->name('examination.')->group(function () {
    Route::get('subjects', [ExamSubjectController::class, 'index'])->name('subjects.index');
    Route::post('subjects', [ExamSubjectController::class, 'store'])->name('subjects.store');
    Route::delete('subjects/{id}', [ExamSubjectController::class, 'destroy'])->name('subjects.destroy');
    
    Route::resource('exam-types', ExamTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('exams', ExamController::class)->only(['index', 'store', 'destroy']);
    Route::resource('grades', GradeController::class)->only(['index', 'store', 'update', 'destroy']);

    // Mark Entry
    Route::get('marks', [ExamController::class, 'marksEntry'])->name('marks.index');
    Route::get('marks/enter', [ExamController::class, 'enterMarks'])->name('marks.entry');
    Route::post('marksGroup', [ExamController::class, 'storeMarks'])->name('marks.store');

    // Tabulation
    Route::get('exams/{exam}/tabulate', [ExamController::class, 'tabulate'])->name('exams.tabulate');
});

// Subject Management
Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);

// User Management
Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

// Student Enquiry Management
Route::resource('student-enquiries', \App\Http\Controllers\School\StudentEnquiryController::class);

// Student Registration Management
Route::get('student-registrations/enquiry/{id}', [\App\Http\Controllers\School\StudentRegistrationController::class, 'getEnquiryData'])->name('student-registrations.enquiry-data');
Route::get('student-registrations/registration-fee/{classId}', [\App\Http\Controllers\School\StudentRegistrationController::class, 'getRegistrationFee'])->name('student-registrations.registration-fee');
Route::get('student-registrations/{id}/pdf', [\App\Http\Controllers\School\StudentRegistrationController::class, 'downloadPdf'])->name('student-registrations.pdf');
Route::resource('student-registrations', \App\Http\Controllers\School\StudentRegistrationController::class);

// Admission Management
Route::get('admission/registration/{id}', [\App\Http\Controllers\School\AdmissionController::class, 'getRegistrationData'])->name('admission.getRegistrationData');
Route::get('admission/{id}/pdf', [\App\Http\Controllers\School\AdmissionController::class, 'downloadPdf'])->name('admission.pdf');
Route::get('admission/class-data/{classId}', [\App\Http\Controllers\School\AdmissionController::class, 'getClassData'])->name('admission.class-data');
Route::resource('admission', \App\Http\Controllers\School\AdmissionController::class)->parameters([
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

// Attendance Reporting
Route::prefix('reports/attendance')->name('reports.attendance.')->group(function () {
    Route::get('/monthly', [AttendanceReportController::class, 'monthly'])->name('monthly');
    Route::get('/student', [AttendanceReportController::class, 'student'])->name('student');
    Route::get('/daily', [AttendanceReportController::class, 'daily'])->name('daily');
});

// Facility Management
Route::prefix('facilities')->name('facilities.')->group(function () {
    Route::get('/', [\App\Http\Controllers\School\StudentFacilityController::class, 'index'])->name('index');
    Route::post('/transport/{student}', [\App\Http\Controllers\School\StudentFacilityController::class, 'assignTransport'])->name('transport.assign');
    Route::post('/hostel/{student}', [\App\Http\Controllers\School\StudentFacilityController::class, 'assignHostel'])->name('hostel.assign');
});

// Other school admin routes...

