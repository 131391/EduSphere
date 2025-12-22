<?php

namespace App\Http\Controllers\Receptionist;

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

        return view('receptionist.student-registrations.index', compact('registrations', 'stats', 'classes'));
    }

    public function create()
    {
        $school = Auth::user()->school;
        $classes = ClassModel::where('school_id', $school->id)->with('registrationFee')->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $enquiries = StudentEnquiry::where('school_id', $school->id)
            ->where('form_status', 'pending')
            ->get();
            
        $studentTypes = StudentType::where('school_id', $school->id)->get();
        $bloodGroups = BloodGroup::where('school_id', $school->id)->get();
        $religions = Religion::where('school_id', $school->id)->get();
        $categories = Category::where('school_id', $school->id)->get();
        $boardingTypes = BoardingType::where('school_id', $school->id)->get();
        $correspondingRelatives = CorrespondingRelative::where('school_id', $school->id)->get();
        $qualifications = Qualification::where('school_id', $school->id)->get();

        return view('receptionist.student-registrations.create', compact(
            'classes', 'academicYears', 'enquiries', 'studentTypes', 
            'bloodGroups', 'religions', 'categories', 'boardingTypes', 
            'correspondingRelatives', 'qualifications'
        ));
    }

    public function store(Request $request)
    {
        $school = Auth::user()->school;

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
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

        return redirect()->route('receptionist.student-registrations.index')->with('success', 'Student registered successfully.');
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

        return view('receptionist.student-registrations.edit', compact(
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
            'admission_status' => 'required|in:Pending,Admitted,Cancelled',
        ]);

        $data = $request->all();

        // Handle File Uploads
        $fileFields = ['father_photo', 'mother_photo', 'student_photo', 'father_signature', 'mother_signature', 'student_signature'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($studentRegistration->$field) {
                    Storage::disk('public')->delete($studentRegistration->$field);
                }
                $path = $request->file($field)->store("registrations/{$school->id}/" . (Str::contains($field, 'photo') ? 'photos' : 'signatures'), 'public');
                $data[$field] = $path;
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
}
