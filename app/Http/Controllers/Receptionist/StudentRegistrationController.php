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
    use HandlesFileCopies, HasAjaxDataTable;
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;
    }

    public function index(Request $request)
    {
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
        $school = Auth::user()->school;
        $classes = ClassModel::where('school_id', $school->id)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $enquiries = StudentEnquiry::where('school_id', $school->id)
            ->pending()
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
        \Illuminate\Support\Facades\Log::info('StudentRegistrationController@store reached', [
            'request' => $request->all(),
            'session' => session()->all()
        ]);

        $school = Auth::user()->school;

        try {
            $validated = $request->validate([
                // Registration Form Information
                'enquiry_id' => 'nullable|exists:student_enquiries,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'class_id' => 'required|exists:classes,id',
                'registration_fee' => 'nullable|numeric',

                // Personal Information
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => ['required', 'integer', Rule::enum(Gender::class)],
                'dob' => 'nullable|date',
                'email' => 'nullable|email|max:150',
                'mobile_no' => 'required|string|max:20',

                // Father's Details
                'father_first_name' => 'required|string|max:100',
                'father_last_name' => 'required|string|max:100',
                'father_mobile_no' => 'required|string|max:20',

                // Mother's Details
                'mother_first_name' => 'required|string|max:100',
                'mother_last_name' => 'required|string|max:100',
                'mother_mobile_no' => 'required|string|max:20',

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
                'father_signature' => 'nullable|image|max:2048',
                'mother_signature' => 'nullable|image|max:2048',
                'student_signature' => 'nullable|image|max:2048',
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
            $data = $request->all();
            $data['school_id'] = $school->id;

            // Handle File Uploads or Copies using Trait
            $data['father_photo'] = $this->storeTenantFile($request->file('father_photo'), "registrations/{$school->id}/photos", $request->input('enquiry_father_photo'));
            $data['mother_photo'] = $this->storeTenantFile($request->file('mother_photo'), "registrations/{$school->id}/photos", $request->input('enquiry_mother_photo'));
            $data['student_photo'] = $this->storeTenantFile($request->file('student_photo'), "registrations/{$school->id}/photos", $request->input('enquiry_student_photo'));
            $data['father_signature'] = $this->storeTenantFile($request->file('father_signature'), "registrations/{$school->id}/signatures", $request->input('enquiry_father_signature'));
            $data['mother_signature'] = $this->storeTenantFile($request->file('mother_signature'), "registrations/{$school->id}/signatures", $request->input('enquiry_mother_signature'));
            $data['student_signature'] = $this->storeTenantFile($request->file('student_signature'), "registrations/{$school->id}/signatures", $request->input('enquiry_student_signature'));

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
                $fee = Fee::create([
                    'school_id' => $school->id,
                    'registration_id' => $registration->id,
                    'academic_year_id' => $registration->academic_year_id,
                    'fee_type_id' => $regFeeName->fee_type_id,
                    'fee_name_id' => $regFeeName->id,
                    'class_id' => $registration->class_id,
                    'bill_no' => 'REG-' . time(),
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

                $cashPaymentMethod = \App\Models\PaymentMethod::where('name', 'Cash')->first();

                FeePayment::create([
                    'school_id' => $school->id,
                    'fee_id' => $fee->id,
                    'academic_year_id' => $registration->academic_year_id,
                    'amount' => $request->registration_fee,
                    'payment_date' => now(),
                    'payment_method_id' => $cashPaymentMethod->id ?? 1,
                    'receipt_no' => 'R-' . time(),
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
        return view('receptionist.student-registrations.show', compact('studentRegistration'));
    }

    public function edit(StudentRegistration $studentRegistration)
    {
        $school = Auth::user()->school;
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
        $school = Auth::user()->school;

        try {
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'class_id' => 'required|exists:classes,id',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'mobile_no' => 'required|string|max:20',
                'admission_status' => ['required', Rule::enum(AdmissionStatus::class)],
                'permanent_country_id' => 'nullable|exists:countries,id',
                'permanent_state_id' => 'nullable|exists:states,id',
                'permanent_city_id' => 'nullable|exists:cities,id',
                'correspondence_country_id' => 'nullable|exists:countries,id',
                'correspondence_state_id' => 'nullable|exists:states,id',
                'correspondence_city_id' => 'nullable|exists:cities,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // New file uploaded - store it
                if ($studentRegistration->$field) {
                    Storage::disk('public')->delete($studentRegistration->$field);
                }
                $path = $request->file($field)->store("registrations/{$school->id}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
                $data[$field] = $path;
            } elseif ($request->filled("enquiry_{$field}")) {
                // Photo from enquiry - copy it to registration storage
                $enquiryPath = $request->input("enquiry_{$field}");
                if ($enquiryPath && Storage::disk('public')->exists($enquiryPath)) {
                    // Delete old file if exists
                    if ($studentRegistration->$field) {
                        Storage::disk('public')->delete($studentRegistration->$field);
                    }
                    // Determine destination directory
                    $destinationDir = "registrations/{$school->id}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures');
                    // Generate new filename to avoid conflicts
                    $filename = basename($enquiryPath);
                    $newPath = $destinationDir . '/' . time() . '_' . $filename;

                    // Copy file from enquiry storage to registration storage
                    Storage::disk('public')->copy($enquiryPath, $newPath);
                    $data[$field] = $newPath;
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
        // Delete files
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($studentRegistration->$field) {
                Storage::disk('public')->delete($studentRegistration->$field);
            }
        }

        $studentRegistration->delete();

        return redirect()->route('receptionist.student-registrations.index')->with('success', 'Registration deleted successfully.');
    }

    public function getEnquiryData($id)
    {
        $school = Auth::user()->school;
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
        try {
            $class = ClassModel::with('registrationFee')->findOrFail($classId);

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
        $school = Auth::user()->school;

        $studentRegistration = StudentRegistration::with(['class', 'academicYear'])
            ->where('school_id', $school->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.student-registration', compact('studentRegistration', 'school'));

        return $pdf->download('student-registration-' . $studentRegistration->registration_no . '.pdf');
    }

    public function downloadTemplate()
    {
        return back()->with('info', 'The CSV template for registration import is being prepared. Please check back shortly.');
    }

    public function import(Request $request)
    {
        return back()->with('info', 'Bulk import feature for student registrations is currently under maintenance. Please register students individually for now.');
    }
}
