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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Enums\AdmissionStatus;
use App\Enums\EnquiryStatus;
use App\Enums\Gender;

class StudentRegistrationController extends TenantController
{
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = StudentRegistration::where('school_id', $schoolId)
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
            'total' => StudentRegistration::where('school_id', $schoolId)->count(),
            'admitted' => StudentRegistration::where('school_id', $schoolId)->admitted()->count(),
            'pending' => StudentRegistration::where('school_id', $schoolId)->pending()->count(),
            'cancelled' => StudentRegistration::where('school_id', $schoolId)->cancelled()->count(),
            'total_enquiry' => StudentEnquiry::where('school_id', $schoolId)->count(),
        ];

        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('receptionist.student-registrations.index', compact('registrations', 'stats', 'classes'));
    }

    public function create()
    {
        $schoolId = $this->getSchoolId();
        $classes = ClassModel::where('school_id', $schoolId)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $enquiries = StudentEnquiry::where('school_id', $schoolId)
            ->pending()
            ->get();
            
        $studentTypes = StudentType::where('school_id', $schoolId)->get();
        $bloodGroups = BloodGroup::where('school_id', $schoolId)->get();
        $religions = Religion::where('school_id', $schoolId)->get();
        $categories = Category::where('school_id', $schoolId)->get();
        $boardingTypes = BoardingType::where('school_id', $schoolId)->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $schoolId)->get();
        $qualifications = Qualification::where('school_id', $schoolId)->get();

        return view('receptionist.student-registrations.create', compact(
            'classes', 'academicYears', 'enquiries', 'studentTypes', 
            'bloodGroups', 'religions', 'categories', 'boardingTypes', 
            'correspondingRelatives', 'qualifications'
        ));
    }

    public function store(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => ['required', 'integer', Rule::enum(Gender::class)],
            'mobile_no' => 'required|string|max:20',
            'father_first_name' => 'required|string|max:100',
            'father_last_name' => 'required|string|max:100',
            'father_mobile_no' => 'required|string|max:20',
            'mother_first_name' => 'required|string|max:100',
            'mother_last_name' => 'required|string|max:100',
            'mother_mobile_no' => 'required|string|max:20',
            'permanent_address' => 'required|string',
            'permanent_state' => 'required|string|max:100',
            'permanent_city' => 'required|string|max:100',
            'permanent_pin' => 'required|string|max:20',
        ]);

        $data = $request->all();
        $data['school_id'] = $schoolId;

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // New file uploaded - store it
                $path = $request->file($field)->store("registrations/{$schoolId}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
                $data[$field] = $path;
            } elseif ($request->filled("enquiry_{$field}")) {
                // Photo from enquiry - copy it to registration storage
                $enquiryPath = $request->input("enquiry_{$field}");
                if ($enquiryPath && Storage::disk('public')->exists($enquiryPath)) {
                    // Determine destination directory
                    $destinationDir = "registrations/{$schoolId}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures');
                    // Generate new filename to avoid conflicts
                    $filename = basename($enquiryPath);
                    $newPath = $destinationDir . '/' . time() . '_' . $filename;
                    
                    // Copy file from enquiry storage to registration storage
                    Storage::disk('public')->copy($enquiryPath, $newPath);
                    $data[$field] = $newPath;
                }
            }
        }

        StudentRegistration::create($data);

        return redirect()->route('receptionist.student-registrations.index')->with('success', 'Student registered successfully.');
    }

    public function show(StudentRegistration $studentRegistration)
    {
        return view('receptionist.student-registrations.show', compact('studentRegistration'));
    }

    public function edit(StudentRegistration $studentRegistration)
    {
        $schoolId = $this->getSchoolId();
        $classes = ClassModel::where('school_id', $schoolId)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $enquiries = StudentEnquiry::where('school_id', $schoolId)->get();
        $studentTypes = StudentType::where('school_id', $schoolId)->get();
        $bloodGroups = BloodGroup::where('school_id', $schoolId)->get();
        $religions = Religion::where('school_id', $schoolId)->get();
        $categories = Category::where('school_id', $schoolId)->get();
        $boardingTypes = BoardingType::where('school_id', $schoolId)->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $schoolId)->get();
        $qualifications = Qualification::where('school_id', $schoolId)->get();

        return view('receptionist.student-registrations.edit', compact(
            'studentRegistration', 'classes', 'academicYears', 'enquiries', 
            'studentTypes', 'bloodGroups', 'religions', 'categories', 
            'boardingTypes', 'correspondingRelatives', 'qualifications'
        ));
    }

    public function update(Request $request, StudentRegistration $studentRegistration)
    {
        $schoolId = $this->getSchoolId();

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
                // New file uploaded - store it
                if ($studentRegistration->$field) {
                    Storage::disk('public')->delete($studentRegistration->$field);
                }
                $path = $request->file($field)->store("registrations/{$schoolId}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
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
                    $destinationDir = "registrations/{$schoolId}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures');
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

        return redirect()->route('receptionist.student-registrations.index')->with('success', 'Registration updated successfully.');
    }

    public function destroy(StudentRegistration $studentRegistration)
    {
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
        $schoolId = $this->getSchoolId();
        $enquiry = StudentEnquiry::where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        if (!$enquiry) {
            return response()->json(['error' => 'Enquiry not found'], 404);
        }
        
        // Format dates for JavaScript
        $data = $enquiry->toArray();
        if ($enquiry->dob) {
            $data['dob'] = $enquiry->dob->format('Y-m-d');
        }
        if ($enquiry->follow_up_date) {
            $data['follow_up_date'] = $enquiry->follow_up_date->format('Y-m-d');
        }
        if ($enquiry->enquiry_date) {
            $data['enquiry_date'] = $enquiry->enquiry_date->format('Y-m-d');
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
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
        $schoolId = $this->getSchoolId();
        $school = $this->getSchool();
        
        $studentRegistration = StudentRegistration::with(['class', 'academicYear'])
            ->where('school_id', $schoolId)
            ->findOrFail($id);
        
        $pdf = \PDF::loadView('pdf.student-registration', compact('studentRegistration', 'school'));
        
        return $pdf->download('student-registration-' . $studentRegistration->registration_no . '.pdf');
    }
}
