<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Enums\AdmissionStatus;
use App\Enums\Gender;

class StudentRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $school = Auth::user()->school;
        
        $query = StudentRegistration::where('school_id', $school->id)
            ->with(['class', 'academicYear']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('admission_status')) {
            $query->where('admission_status', $request->admission_status);
        }

        $registrations = $query->orderBy('id', 'desc')->paginate(10);

        // Statistics
        $stats = [
            'total' => StudentRegistration::where('school_id', $school->id)->count(),
            'admitted' => StudentRegistration::where('school_id', $school->id)->admitted()->count(),
            'pending' => StudentRegistration::where('school_id', $school->id)->pending()->count(),
            'cancelled' => StudentRegistration::where('school_id', $school->id)->cancelled()->count(),
            'total_enquiry' => StudentEnquiry::where('school_id', $school->id)->count(),
        ];

        $classes = ClassModel::where('school_id', $school->id)->get();

        return view('school.student-registrations.index', compact('registrations', 'stats', 'classes'));
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

        return view('school.student-registrations.create', compact(
            'classes', 'academicYears', 'enquiries', 'studentTypes', 
            'bloodGroups', 'religions', 'categories', 'boardingTypes', 
            'correspondingRelatives', 'qualifications'
        ));
    }

    public function store(Request $request)
    {
        $school = Auth::user()->school;

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
            'permanent_state' => 'required|string|max:100',
            'permanent_city' => 'required|string|max:100',
            'permanent_pin' => 'required|string|max:20',
            
            // Photos & Signatures
            'father_photo' => 'nullable|image|max:2048',
            'mother_photo' => 'nullable|image|max:2048',
            'student_photo' => 'nullable|image|max:2048',
            'father_signature' => 'nullable|image|max:2048',
            'mother_signature' => 'nullable|image|max:2048',
            'student_signature' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['school_id'] = $school->id;

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("registrations/{$school->id}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
                $data[$field] = $path;
            }
        }

        StudentRegistration::create($data);

        return redirect()->route('school.student-registrations.index')->with('success', 'Student registered successfully.');
    }

    public function show(StudentRegistration $studentRegistration)
    {
        return view('school.student-registrations.show', compact('studentRegistration'));
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

        return view('school.student-registrations.edit', compact(
            'studentRegistration', 'classes', 'academicYears', 'enquiries', 
            'studentTypes', 'bloodGroups', 'religions', 'categories', 
            'boardingTypes', 'correspondingRelatives', 'qualifications'
        ));
    }

    public function update(Request $request, StudentRegistration $studentRegistration)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile_no' => 'required|string|max:20',
            'admission_status' => ['required', Rule::enum(AdmissionStatus::class)],
        ]);

        $data = $request->all();

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($studentRegistration->$field) {
                    Storage::disk('public')->delete($studentRegistration->$field);
                }
                $path = $request->file($field)->store("registrations/{$school->id}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
                $data[$field] = $path;
            }
        }

        $studentRegistration->update($data);

        return redirect()->route('school.student-registrations.index')->with('success', 'Registration updated successfully.');
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

        return redirect()->route('school.student-registrations.index')->with('success', 'Registration deleted successfully.');
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
        
        $pdf = \PDF::loadView('pdf.student-registration', compact('studentRegistration', 'school'));
        
        return $pdf->download('student-registration-' . $studentRegistration->registration_no . '.pdf');
    }
}
