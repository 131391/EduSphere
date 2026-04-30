<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\StudentRegistration;
use App\Models\ClassModel;
use App\Models\AcademicYear;
use App\Models\StudentEnquiry;
use App\Models\StudentType;
use App\Models\BloodGroup;
use App\Models\Religion;
use App\Models\Category;
use App\Models\BoardingType;
use App\Models\CorrespondingRelative;
use App\Models\Qualification;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeePayment;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Traits\HandlesFileCopies;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Traits\HasAjaxDataTable;

class StudentRegistrationController extends TenantController
{
    use HandlesFileCopies, HasAjaxDataTable, \App\Traits\HandlesFinancialNumbers;
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', StudentRegistration::class);
        $schoolId = $this->getSchoolId();

        // 1. Row Transformer (Gold Standard UI consistency)
        $transformer = function ($reg) {
            $status = $reg->admission_status;

            // Map Tailwind-style config for badges
            // Defensively handle null or non-enum status to prevent 500 errors
            $color = ($status instanceof AdmissionStatus) ? $status->color() : 'gray';

            $statusConfig = [
                'bg' => "bg-{$color}-50",
                'text' => "text-{$color}-700",
                'border' => "border-{$color}-100",
                'icon' => match ($status) {
                    AdmissionStatus::Pending => 'fa-clock',
                    AdmissionStatus::Admitted => 'fa-user-check',
                    AdmissionStatus::Cancelled => 'fa-times-circle',
                    default => 'fa-question-circle'
                }
            ];

            return [
                'id' => $reg->id,
                'registration_no' => $reg->registration_no,
                'full_name' => $reg->full_name,
                'initials' => collect(explode(' ', $reg->full_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'mobile_no' => $reg->mobile_no,
                'email' => $reg->email ?? 'N/A',
                'father_name' => $reg->father_first_name . ' ' . $reg->father_last_name,
                'class_name' => $reg->class?->name ?? 'N/A',
                'academic_year' => $reg->academicYear?->name ?? 'N/A',
                'registration_fee' => number_format($reg->registration_fee, 2),
                'registration_date' => $reg->registration_date->format('d M, Y'),
                'status_label' => ($status instanceof AdmissionStatus) ? $status->label() : 'Pending',
                'status_config' => $statusConfig,
                'student_photo' => $reg->student_photo ? asset('storage/' . $reg->student_photo) : null,
            ];
        };

        // 2. Build Query
        $query = StudentRegistration::where('school_id', $schoolId)
            ->with(['class', 'academicYear']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('admission_status')) {
            $query->where('admission_status', $request->admission_status);
        }

        // 3. Handle AJAX or CSV Export vs Blade Hydration
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $this->getStats($schoolId));
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        // 4. Blade Hydration
        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $this->getStats($schoolId)
        ]);

        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('receptionist.student-registrations.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'classes' => $classes,
        ]);
    }

    /**
     * Get aggregate statistics for the registry
     */
    private function getStats(int $schoolId): array
    {
        return [
            'total' => StudentRegistration::where('school_id', $schoolId)->count(),
            'admitted' => StudentRegistration::where('school_id', $schoolId)->admitted()->count(),
            'pending' => StudentRegistration::where('school_id', $schoolId)->pending()->count(),
            'cancelled' => StudentRegistration::where('school_id', $schoolId)->cancelled()->count(),
            'total_enquiry' => StudentEnquiry::where('school_id', $schoolId)->count(),
        ];
    }


    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_registrations_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Reg No', 'Student Name', 'Class', 'Father Name', 'Mobile', 'Fee', 'Status', 'Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function ($reg) use ($file) {
                fputcsv($file, [
                    $reg->registration_no,
                    $reg->full_name,
                    $reg->class?->name ?? 'N/A',
                    $reg->father_first_name . ' ' . $reg->father_last_name,
                    $reg->mobile_no,
                    $reg->registration_fee,
                    $reg->admission_status?->label() ?? 'Pending',
                    $reg->registration_date->format('Y-m-d')
                ]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->authorize('create', StudentRegistration::class);
        $school = $this->getSchool();
        $classes = ClassModel::where('school_id', $school->id)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $enquiries = StudentEnquiry::where('school_id', $school->id)
            ->whereIn('form_status', [
                \App\Enums\EnquiryStatus::Pending,
                \App\Enums\EnquiryStatus::Completed,
            ])
            ->get();

        $studentTypes = StudentType::where('school_id', $school->id)->get();
        $bloodGroups = BloodGroup::where('school_id', $school->id)->get();
        $religions = Religion::where('school_id', $school->id)->get();
        $categories = Category::where('school_id', $school->id)->get();
        $boardingTypes = BoardingType::where('school_id', $school->id)->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $school->id)->get();
        $qualifications = Qualification::where('school_id', $school->id)->get();
        $countries = $this->locationService->getCountries();

        return view('receptionist.student-registrations.create', compact(
            'classes',
            'academicYears',
            'enquiries',
            'studentTypes',
            'bloodGroups',
            'religions',
            'categories',
            'boardingTypes',
            'correspondingRelatives',
            'qualifications',
            'countries'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', StudentRegistration::class);
        $school = $this->getSchool();

        try {
            $validated = $request->validate([
                // Registration Form Information
                'enquiry_id' => [
                    'nullable',
                    Rule::exists('student_enquiries', 'id')->where('school_id', $school->id)
                ],
                'academic_year_id' => [
                    'required',
                    Rule::exists('academic_years', 'id')->where('school_id', $school->id)
                ],
                'class_id' => [
                    'required',
                    Rule::exists('classes', 'id')->where('school_id', $school->id)
                ],
                'registration_fee' => 'nullable|numeric',

                // Personal Information
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => ['required', 'integer', Rule::enum(Gender::class)],
                'dob' => 'nullable|date',
                'email' => 'nullable|email|max:150',
                'mobile_no' => 'required|string|max:20',
                'aadhaar_no' => 'nullable|digits:12',

                // Father's Details
                'father_first_name' => 'required|string|max:100',
                'father_last_name' => 'required|string|max:100',
                'father_mobile_no' => 'required|string|max:20',
                'father_aadhaar_no' => 'nullable|digits:12',

                // Mother's Details
                'mother_first_name' => 'required|string|max:100',
                'mother_last_name' => 'required|string|max:100',
                'mother_mobile_no' => 'required|string|max:20',
                'mother_aadhaar_no' => 'nullable|digits:12',

                // Permanent Address
                'permanent_address' => 'required|string',
                'permanent_country_id' => 'required',
                'permanent_state_id' => 'required',
                'permanent_city_id' => 'required',
                'permanent_pin' => 'required|string|max:20',
                'correspondence_address' => 'nullable|string',
                'correspondence_country_id' => 'nullable|exists:countries,id',
                'correspondence_state_id' => 'nullable|exists:states,id',
                'correspondence_city_id' => 'nullable|exists:cities,id',

                // Photos & Signatures
                'father_photo' => 'nullable|image|max:2048',
                'mother_photo' => 'nullable|image|max:2048',
                'student_photo' => 'nullable|image|max:2048',
                'student_signature' => 'nullable|image|max:2048',

                // Normalized Master Data IDs
                'blood_group_id' => ['nullable', Rule::exists('blood_groups', 'id')->where('school_id', $school->id)],
                'religion_id' => ['nullable', Rule::exists('religions', 'id')->where('school_id', $school->id)],
                'category_id' => ['nullable', Rule::exists('categories', 'id')->where('school_id', $school->id)],
                'student_type_id' => ['nullable', Rule::exists('student_types', 'id')->where('school_id', $school->id)],
                'corresponding_relative_id' => ['nullable', Rule::exists('corresponding_relatives', 'id')->where('school_id', $school->id)],
                'boarding_type_id' => ['nullable', Rule::exists('boarding_types', 'id')->where('school_id', $school->id)],
                'father_qualification_id' => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $school->id)],
                'mother_qualification_id' => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $school->id)],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Pre-check Registration Fee setup if fee is provided
        if ($request->registration_fee > 0) {
            $regFeeName = FeeName::where('school_id', $school->id)
                ->where('name', 'Registration Fee')
                ->first();

            if (!$regFeeName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Financial system error',
                    'errors' => ['registration_fee' => ['Financial system error: "Registration Fee" type not found in school settings. Please contact administrator.']]
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Duplicate detection — Aadhaar and mobile across existing registrations
            if ($request->aadhaar_no) {
                $exists = \App\Models\StudentRegistration::where('school_id', $school->id)
                    ->where('aadhaar_no', $request->aadhaar_no)
                    ->exists();
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation Failed',
                        'errors' => ['aadhaar_no' => ["A registration with Aadhaar number [{$request->aadhaar_no}] already exists."]]
                    ], 422);
                }
            }
            if ($request->mobile_no) {
                $exists = \App\Models\StudentRegistration::where('school_id', $school->id)
                    ->where('mobile_no', $request->mobile_no)
                    ->exists();
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation Failed',
                        'errors' => ['mobile_no' => ["A registration with mobile number [{$request->mobile_no}] already exists."]]
                    ], 422);
                }
            }

            $data = $request->all();
            $data['school_id'] = $school->id;
            $registrationPhotoSourcePrefixes = ['enquiries/photos'];

            // Handle File Uploads or Copies using Trait
            $data['father_photo'] = $this->storeTenantFile($request->file('father_photo'), "registrations/{$school->id}/photos")
                ?? $this->copyTenantFile($request->input('enquiry_father_photo'), "registrations/{$school->id}/photos", $registrationPhotoSourcePrefixes);
            $data['mother_photo'] = $this->storeTenantFile($request->file('mother_photo'), "registrations/{$school->id}/photos")
                ?? $this->copyTenantFile($request->input('enquiry_mother_photo'), "registrations/{$school->id}/photos", $registrationPhotoSourcePrefixes);
            $data['student_photo'] = $this->storeTenantFile($request->file('student_photo'), "registrations/{$school->id}/photos")
                ?? $this->copyTenantFile($request->input('enquiry_student_photo'), "registrations/{$school->id}/photos", $registrationPhotoSourcePrefixes);
            $data['father_signature'] = $this->storeTenantFile($request->file('father_signature'), "registrations/{$school->id}/signatures");
            $data['mother_signature'] = $this->storeTenantFile($request->file('mother_signature'), "registrations/{$school->id}/signatures");
            $data['student_signature'] = $this->storeTenantFile($request->file('student_signature'), "registrations/{$school->id}/signatures");

            $registration = StudentRegistration::create($data);

            // Update Enquiry Status if linked
            if ($request->filled('enquiry_id')) {
                $enquiry = StudentEnquiry::where('school_id', $school->id)
                    ->where('id', $request->enquiry_id)
                    ->first();
                if ($enquiry) {
                    $enquiry->update(['form_status' => \App\Enums\EnquiryStatus::Completed]);
                }
            }

            // --- FINANCIAL INTEGRATION ---
            if ($request->registration_fee > 0) {
                // $regFeeName already found above
                $fee = Fee::forceCreate([
                    'school_id' => $school->id,
                    'registration_id' => $registration->id,
                    'academic_year_id' => $registration->academic_year_id,
                    'fee_type_id' => $regFeeName->fee_type_id,
                    'fee_name_id' => $regFeeName->id,
                    'class_id' => $registration->class_id,
                    'bill_no' => $this->generateBillNumber($school->id),
                    'fee_period' => 'One-time',
                    'payable_amount' => $request->registration_fee,
                    'paid_amount' => $request->registration_fee,
                    'due_amount' => 0,
                    'due_date' => now(),
                    'payment_date' => now(),
                    'payment_status' => \App\Enums\FeeStatus::Paid,
                    'payment_mode' => 'Cash',
                    'remarks' => 'Registration Fee for No: ' . $registration->registration_no
                ]);

                $cashPaymentMethod = \App\Models\PaymentMethod::where('school_id', $school->id)
                    ->where('name', 'Cash')
                    ->first();

                if (!$cashPaymentMethod) {
                    throw new \Exception('No Cash payment method configured for this school.');
                }

                FeePayment::create([
                    'school_id' => $school->id,
                    'fee_id' => $fee->id,
                    'academic_year_id' => $registration->academic_year_id,
                    'amount' => $request->registration_fee,
                    'payment_date' => now(),
                    'payment_method_id' => $cashPaymentMethod->id,
                    'receipt_no' => $this->generateReceiptNumber($school->id),
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully with financial records.',
                'redirect' => route('receptionist.student-registrations.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Registration Error: " . $e->getMessage());

            // Try to provide a more helpful field-level error if it's a common issue
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Registration Fee')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Financial system error',
                    'errors' => ['registration_fee' => ['Error calculating registration fee: ' . $errorMessage]]
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Critical Error: ' . $errorMessage
            ], 500);
        }
    }


    public function show(StudentRegistration $studentRegistration)
    {
        $this->authorizeTenant($studentRegistration);
        $this->authorize('view', $studentRegistration);
        return view('receptionist.student-registrations.show', compact('studentRegistration'));
    }

    public function edit(StudentRegistration $studentRegistration)
    {
        $this->authorizeTenant($studentRegistration);
        $this->authorize('update', $studentRegistration);
        $school = $this->getSchool();
        $classes = ClassModel::where('school_id', $school->id)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $enquiries = StudentEnquiry::where('school_id', $school->id)->get();
        $studentTypes = StudentType::where('school_id', $school->id)->get();
        $bloodGroups = BloodGroup::where('school_id', $school->id)->get();
        $religions = Religion::where('school_id', $school->id)->get();
        $categories = Category::where('school_id', $school->id)->get();
        $boardingTypes = BoardingType::where('school_id', $school->id)->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $school->id)->get();
        $qualifications = Qualification::where('school_id', $school->id)->get();
        $countries = $this->locationService->getCountries();

        return view('receptionist.student-registrations.edit', compact(
            'studentRegistration',
            'classes',
            'academicYears',
            'enquiries',
            'studentTypes',
            'bloodGroups',
            'religions',
            'categories',
            'boardingTypes',
            'correspondingRelatives',
            'qualifications',
            'countries'
        ));
    }

    public function update(Request $request, StudentRegistration $studentRegistration)
    {
        $this->authorizeTenant($studentRegistration);
        $this->authorize('update', $studentRegistration);
        $school = $this->getSchool();

        try {
            $validated = $request->validate([
                'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $school->id)],
                'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $school->id)],
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'mobile_no' => 'required|string|max:20',
                'aadhaar_no' => 'nullable|digits:12',
                'father_aadhaar_no' => 'nullable|digits:12',
                'mother_aadhaar_no' => 'nullable|digits:12',
                'admission_status' => ['required', Rule::enum(AdmissionStatus::class)],
                'permanent_country_id' => 'nullable|exists:countries,id',
                'permanent_state_id' => 'nullable|exists:states,id',
                'permanent_city_id' => 'nullable|exists:cities,id',
                'correspondence_country_id' => 'nullable|exists:countries,id',
                'correspondence_state_id' => 'nullable|exists:states,id',
                'correspondence_city_id' => 'nullable|exists:cities,id',

                // Normalized Master Data IDs
                'blood_group_id' => ['nullable', Rule::exists('blood_groups', 'id')->where('school_id', $school->id)],
                'religion_id' => ['nullable', Rule::exists('religions', 'id')->where('school_id', $school->id)],
                'category_id' => ['nullable', Rule::exists('categories', 'id')->where('school_id', $school->id)],
                'student_type_id' => ['nullable', Rule::exists('student_types', 'id')->where('school_id', $school->id)],
                'corresponding_relative_id' => ['nullable', Rule::exists('corresponding_relatives', 'id')->where('school_id', $school->id)],
                'boarding_type_id' => ['nullable', Rule::exists('boarding_types', 'id')->where('school_id', $school->id)],
                'father_qualification_id' => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $school->id)],
                'mother_qualification_id' => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $school->id)],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        }

        $data = $request->all();
        $registrationPhotoDirectory = "registrations/{$school->id}/photos";
        $registrationSignatureDirectory = "registrations/{$school->id}/signatures";
        $registrationPhotoPrefixes = [$registrationPhotoDirectory];
        $registrationSignaturePrefixes = [$registrationSignatureDirectory];

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $directory = Str::contains($field, 'photo') ? $registrationPhotoDirectory : $registrationSignatureDirectory;
                $allowedExistingPrefixes = Str::contains($field, 'photo') ? $registrationPhotoPrefixes : $registrationSignaturePrefixes;
                $path = $this->replaceTenantFile($request->file($field), $directory, $studentRegistration->$field, $allowedExistingPrefixes);
                $data[$field] = $path;
            } elseif ($request->filled("enquiry_{$field}")) {
                $destinationDir = Str::contains($field, 'photo') ? $registrationPhotoDirectory : $registrationSignatureDirectory;
                $allowedExistingPrefixes = Str::contains($field, 'photo') ? $registrationPhotoPrefixes : $registrationSignaturePrefixes;
                $allowedSourcePrefixes = Str::contains($field, 'photo') ? ['enquiries/photos'] : [];
                $copiedPath = $this->copyTenantFile($request->input("enquiry_{$field}"), $destinationDir, $allowedSourcePrefixes);

                if ($copiedPath) {
                    if ($this->isAllowedPublicPath($studentRegistration->$field, $allowedExistingPrefixes)) {
                        Storage::disk('public')->delete($studentRegistration->$field);
                    }

                    $data[$field] = $copiedPath;
                }
            }
        }

        $studentRegistration->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Registration updated successfully.',
            'redirect' => route('receptionist.student-registrations.index')
        ]);
    }

    public function destroy(StudentRegistration $studentRegistration)
    {
        $this->authorizeTenant($studentRegistration);
        $this->authorize('delete', $studentRegistration);

        if ($studentRegistration->admission_status === AdmissionStatus::Admitted) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete a registration that has already been admitted.'], 422);
            }
            return redirect()->route('receptionist.student-registrations.index')
                ->with('error', 'Cannot delete a registration that has already been admitted.');
        }

        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($studentRegistration->$field) {
                Storage::disk('public')->delete($studentRegistration->$field);
            }
        }

        $studentRegistration->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Registration deleted successfully.']);
        }

        return redirect()->route('receptionist.student-registrations.index')->with('success', 'Registration deleted successfully.');
    }

    public function getEnquiryData($id)
    {
        $this->authorize('viewAny', StudentRegistration::class);

        $school = $this->getSchool();
        $enquiry = StudentEnquiry::where('school_id', $school->id)
            ->where('id', $id)
            ->first();

        if (!$enquiry) {
            return response()->json(['error' => 'Enquiry not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $enquiry
        ]);
    }

    public function getRegistrationFee($classId)
    {
        $this->authorize('viewAny', StudentRegistration::class);

        try {
            // Tenantable scope on ClassModel keeps this per-school; the
            // explicit where is belt-and-suspenders.
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->with('registrationFee')
                ->findOrFail($classId);

            return response()->json([
                'success' => true,
                'fee' => $class->registrationFee->amount ?? 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registration fee'
            ], 404);
        }
    }

    public function downloadPdf($id)
    {
        $school = $this->getSchool();

        $studentRegistration = StudentRegistration::with(['class', 'academicYear'])
            ->where('school_id', $school->id)
            ->findOrFail($id);

        $this->authorize('view', $studentRegistration);

        $pdf = Pdf::loadView('pdf.student-registration', compact('studentRegistration', 'school'));

        return $pdf->download('student-registration-' . $studentRegistration->registration_no . '.pdf');
    }

    public function downloadTemplate()
    {
        $this->authorize('create', StudentRegistration::class);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="registration_template.csv"',
        ];
        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student Name', 'Father Name', 'Mother Name', 'Date of Birth (YYYY-MM-DD)', 'Gender (Male/Female/Other)', 'Class ID', 'Registration Fee']);
            fputcsv($file, ['John Doe', 'Richard Doe', 'Jane Doe', '2015-05-15', 'Male', '1', '500']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $this->authorize('create', StudentRegistration::class);

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $school = $this->getSchool();
        $validClassIds = ClassModel::where('school_id', $school->id)->pluck('id')->toArray();
        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The CSV file is empty.']);
        }
        array_shift($rows);

        $imported = 0;
        $skipped  = 0;
        $rowErrors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                if (count($row) < 7) {
                    $rowErrors[] = "Line {$line}: insufficient columns."; $skipped++; continue;
                }
                [$studentName, $fatherName, $motherName, $dob, $genderStr, $classId, $fee] = $row;
                $classIdInt = (int) trim($classId);

                if (!in_array($classIdInt, $validClassIds)) {
                    $rowErrors[] = "Line {$line}: class_id {$classIdInt} not valid for this school."; $skipped++; continue;
                }
                if (StudentRegistration::where('school_id', $school->id)->where('first_name', trim($studentName))->where('dob', trim($dob))->where('class_id', $classIdInt)->exists()) {
                    $rowErrors[] = "Line {$line}: duplicate — " . trim($studentName) . " already registered."; $skipped++; continue;
                }

                $genderValue = match (strtolower(trim($genderStr))) {
                    'male'   => Gender::Male->value,
                    'female' => Gender::Female->value,
                    default  => null,
                };

                StudentRegistration::create([
                    'school_id'         => $school->id,
                    'first_name'        => trim($studentName),
                    'father_first_name' => trim($fatherName),
                    'mother_first_name' => trim($motherName),
                    'dob'               => trim($dob),
                    'gender'            => $genderValue,
                    'class_id'          => $classIdInt,
                    'registration_fee'  => is_numeric(trim($fee)) ? trim($fee) : 0,
                    'registration_date' => now(),
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }

        $message = "{$imported} registration(s) imported." . ($skipped ? " {$skipped} skipped." : '');
        return redirect()->route('receptionist.student-registrations.index')
            ->with('success', $message)
            ->with('import_errors', $rowErrors);
    }
}
