<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Http\Requests\School\StoreAdmissionRequest;
use App\Http\Requests\School\UpdateAdmissionRequest;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeePayment;
use App\Models\StudentTransportAssignment;
use App\Models\HostelBedAssignment;
use App\Models\BloodGroup;
use App\Models\Religion;
use App\Models\Category;
use App\Models\StudentType;
use App\Models\CorrespondingRelative;
use App\Models\Qualification;
use App\Models\TransportRoute;
use App\Models\Hostel;
use App\Models\Role;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\AdmissionFee;
use App\Traits\HandlesFileCopies;
use App\Enums\GeneralStatus;
use App\Enums\UserStatus;
use App\Enums\FeeStatus;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\StudentEnquiry;
use App\Enums\EnquiryStatus;

use App\Traits\HasAjaxDataTable;
use App\Services\LocationService;
use App\Services\School\AdmissionService;

class AdmissionController extends TenantController
{
    use HandlesFileCopies, HasAjaxDataTable, \App\Traits\HandlesFinancialNumbers;

    public function __construct(
        protected LocationService $locationService,
        protected AdmissionService $admissionService,
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        // 1. Row Transformer (Gold Standard UI consistency)
        $transformer = function ($student) {
            return [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'registration_no' => $student->registration_no ?? 'N/A',
                'full_name' => $student->full_name,
                'initials' => collect(explode(' ', $student->full_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'mobile_no' => $student->mobile_no,
                'email' => $student->email ?? 'N/A',
                'father_name' => $student->father_name,
                'class_name' => $student->class?->name ?? 'N/A',
                'section_name' => $student->section?->name ?? 'A',
                'admission_date' => $student->admission_date ? $student->admission_date->format('d M, Y') : 'N/A',
                'student_photo' => $student->student_photo ? asset('storage/' . $student->student_photo) : null,
                'status_label' => $student->status?->label() ?? 'Active',
                'status_color' => 'teal', // Standard for confirmed admissions
            ];
        };

        // 2. Build Query
        $query = Student::where('school_id', $schoolId)
            ->with(['class', 'section']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_no', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
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
        $sections = Section::where('school_id', $schoolId)->get();

        return view('receptionist.admission.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Get aggregate statistics for the registry
     */
    private function getStats(int $schoolId): array
    {
        return [
            'total_registration' => StudentRegistration::where('school_id', $schoolId)->count(),
            'admission_done' => Student::where('school_id', $schoolId)->count(),
            'pending_registration' => StudentRegistration::where('school_id', $schoolId)->pending()->count(),
            'cancelled_registration' => StudentRegistration::where('school_id', $schoolId)->cancelled()->count(),
            'total_enquiry' => StudentEnquiry::where('school_id', $schoolId)->count(),
        ];
    }


    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_admission_registry_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Father Name', 'Class', 'Section', 'Phone', 'Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function ($student) use ($file) {
                fputcsv($file, [
                    $student->admission_no,
                    $student->full_name,
                    $student->father_name,
                    $student->class?->name ?? 'N/A',
                    $student->section?->name ?? 'N/A',
                    $student->mobile_no,
                    $student->admission_date ? $student->admission_date->format('Y-m-d') : 'N/A'
                ]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();

        // Fetch student registrations for dropdown
        $registrations = StudentRegistration::where('school_id', $this->getSchoolId())
            ->pending()
            ->get();

        // Fetch master data
        $bloodGroups = BloodGroup::where('school_id', $this->getSchoolId())->get();
        $religions = Religion::where('school_id', $this->getSchoolId())->get();
        $categories = Category::where('school_id', $this->getSchoolId())->get();
        $studentTypes = StudentType::where('school_id', $this->getSchoolId())->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $this->getSchoolId())->get();
        $qualifications = Qualification::where('school_id', $this->getSchoolId())->get();
        $transportRoutes = TransportRoute::where('school_id', $this->getSchoolId())->get();
        $hostels = Hostel::where('school_id', $this->getSchoolId())->get();

        // Generate next admission number
        $lastStudent = Student::where('school_id', $this->getSchoolId())->latest()->first();
        $nextAdmissionNo = $lastStudent ? (intval($lastStudent->admission_no) + 1) : 100001;

        $countries = $this->locationService->getCountries();

        return view('receptionist.admission.create', compact(
            'classes',
            'sections',
            'academicYears',
            'nextAdmissionNo',
            'registrations',
            'bloodGroups',
            'religions',
            'categories',
            'studentTypes',
            'correspondingRelatives',
            'qualifications',
            'transportRoutes',
            'hostels',
            'countries'
        ));
    }


    public function store(StoreAdmissionRequest $request)
    {
        $school = $this->getSchool();

        DB::beginTransaction();
        try {
            $this->admissionService->admit($request, $school);

            DB::commit();

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student admitted successfully.',
                    'redirect' => route('receptionist.admission.index')
                ]);
            }
            return redirect()->route('receptionist.admission.index')->with('success', 'Student admitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Admission Error: ' . $e->getMessage());

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function show(Student $student)
    {
        return view('receptionist.admission.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load(['permanentState', 'permanentCity', 'correspondenceState', 'correspondenceCity']);
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();

        // Fetch student registrations (Pending + current student's registration)
        $registrations = StudentRegistration::where('school_id', $this->getSchoolId())
            ->where(function ($query) use ($student) {
                $query->where('admission_status', AdmissionStatus::Pending);
                if ($student->registration_no) {
                    $query->orWhere('registration_no', $student->registration_no);
                }
            })
            ->get();

        // Fetch master data
        $bloodGroups = BloodGroup::where('school_id', $this->getSchoolId())->get();
        $religions = Religion::where('school_id', $this->getSchoolId())->get();
        $categories = Category::where('school_id', $this->getSchoolId())->get();
        $studentTypes = StudentType::where('school_id', $this->getSchoolId())->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $this->getSchoolId())->get();
        $qualifications = Qualification::where('school_id', $this->getSchoolId())->get();
        $transportRoutes = TransportRoute::where('school_id', $this->getSchoolId())->get();
        $hostels = Hostel::where('school_id', $this->getSchoolId())->get();

        $countries = $this->locationService->getCountries();

        return view('receptionist.admission.edit', compact(
            'student',
            'classes',
            'sections',
            'academicYears',
            'registrations',
            'bloodGroups',
            'religions',
            'categories',
            'studentTypes',
            'correspondingRelatives',
            'qualifications',
            'transportRoutes',
            'hostels',
            'countries'
        ));
    }

    public function update(UpdateAdmissionRequest $request, Student $student)
    {
        // Authorization: ensure this student belongs to the current school.
        // UpdateAdmissionRequest already validates all foreign keys are tenant-scoped.
        $this->authorizeTenant($student);

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Exclude fields that don't exist in students table or need special handling
            $excludedFields = [
                'student_photo', 'father_photo', 'mother_photo',
                'student_signature', 'father_signature', 'mother_signature',
                'father_first_name', 'father_middle_name', 'father_last_name',
                'father_mobile_no', 'father_landline', 'father_landline_no',
                'father_organization', 'father_office_address',
                'father_designation',
                'mother_first_name', 'mother_middle_name', 'mother_last_name',
                'mother_mobile_no', 'mother_landline', 'mother_landline_no',
                'mother_organization', 'mother_office_address',
                'mother_designation',
                'father_name_prefix', 'mother_name_prefix',
                'registration_id', 'student_photo_path', 'father_photo_path', 'mother_photo_path',
                'student_signature_path', 'father_signature_path', 'mother_signature_path'
            ];
            
            $data = $request->except($excludedFields);
            $student->fill($data);

            $student->father_name = trim(implode(' ', array_filter([$request->father_first_name, $request->father_middle_name, $request->father_last_name])));
            $student->mother_name = trim(implode(' ', array_filter([$request->mother_first_name, $request->mother_middle_name, $request->mother_last_name])));

            // Handle Student Photo
            if ($request->hasFile('student_photo')) {
                if ($student->student_photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->student_photo);
                }
                $path = $request->file('student_photo')->store('student_photos', 'public');
                $student->student_photo = $path;
            }

            // Handle Father Photo
            if ($request->hasFile('father_photo')) {
                if ($student->father_photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->father_photo);
                }
                $path = $request->file('father_photo')->store('parent_photos', 'public');
                $student->father_photo = $path;
            } elseif ($request->filled('father_photo_path')) {
                $sourcePath = $request->father_photo_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'parent_photos/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->father_photo = $newPath;
                }
            }

            // Handle Mother Photo
            if ($request->hasFile('mother_photo')) {
                if ($student->mother_photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->mother_photo);
                }
                $path = $request->file('mother_photo')->store('parent_photos', 'public');
                $student->mother_photo = $path;
            } elseif ($request->filled('mother_photo_path')) {
                $sourcePath = $request->mother_photo_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'parent_photos/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->mother_photo = $newPath;
                }
            }

            // Handle Student Signature
            if ($request->hasFile('student_signature')) {
                if ($student->student_signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->student_signature);
                }
                $path = $request->file('student_signature')->store('student_signatures', 'public');
                $student->student_signature = $path;
            } elseif ($request->filled('student_signature_path')) {
                $sourcePath = $request->student_signature_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'student_signatures/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->student_signature = $newPath;
                }
            }

            // Handle Father Signature
            if ($request->hasFile('father_signature')) {
                if ($student->father_signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->father_signature);
                }
                $path = $request->file('father_signature')->store('parent_signatures', 'public');
                $student->father_signature = $path;
            } elseif ($request->filled('father_signature_path')) {
                $sourcePath = $request->father_signature_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'parent_signatures/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->father_signature = $newPath;
                }
            }

            // Handle Mother Signature
            if ($request->hasFile('mother_signature')) {
                if ($student->mother_signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->mother_signature);
                }
                $path = $request->file('mother_signature')->store('parent_signatures', 'public');
                $student->mother_signature = $path;
            } elseif ($request->filled('mother_signature_path')) {
                $sourcePath = $request->mother_signature_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'parent_signatures/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->mother_signature = $newPath;
                }
            }

            $student->save();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student details updated successfully.',
                    'redirect' => route('receptionist.admission.index')
                ]);
            }

            return redirect()->route('receptionist.admission.index')->with('success', 'Student details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Update failed: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating student: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $this->authorizeTenant($student);
        try {
            if ($student->fees()->whereHas('payments')->exists()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Cannot delete student with existing fee payment records.'], 422);
                }
                return redirect()->route('receptionist.admission.index')->with('error', 'Cannot delete student with existing fee payment records.');
            }

            $student->delete(); // soft delete

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student record deleted successfully.'
                ]);
            }

            return redirect()->route('receptionist.admission.index')->with('success', 'Student record deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deletion failed: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('receptionist.admission.index')->with('error', 'Deletion failed: ' . $e->getMessage());
        }
    }

    public function getClassData($classId)
    {
        $sections = Section::where('school_id', $this->getSchoolId())
            ->where('class_id', $classId)
            ->get(['id', 'name']);

        $admissionFee = AdmissionFee::where('school_id', $this->getSchoolId())
            ->where('class_id', $classId)
            ->first();

        return response()->json([
            'sections' => $sections,
            'admission_fee' => $admissionFee ? $admissionFee->amount : 0
        ]);
    }

    /**
     * Get registration data for auto-filling admission form
     */
    public function getRegistrationData($registrationId)
    {
        $registration = StudentRegistration::where('id', $registrationId)
            ->where('school_id', $this->getSchoolId())
            ->first();

        if (!$registration) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        // Format dates for JavaScript
        $data = $registration->toArray();
        if ($registration->dob) {
            $data['dob'] = \Carbon\Carbon::parse($registration->dob)->format('Y-m-d');
        }
        if ($registration->registration_date) {
            $data['registration_date'] = \Carbon\Carbon::parse($registration->registration_date)->format('Y-m-d');
        }

        return response()->json($data);
    }

    public function downloadPdf($id)
    {
        $school = $this->getSchool();
        $student = Student::with(['class', 'section', 'academicYear'])
            ->where('school_id', $this->getSchoolId())
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.student-admission', compact('student', 'school'));
        return $pdf->download('student-admission-' . $student->admission_no . '.pdf');
    }

}
