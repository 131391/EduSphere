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
use Illuminate\Support\Str;

class AdmissionController extends TenantController
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['class', 'section'])
            ->where('school_id', $this->getSchoolId());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(10);
        
        // Stats
        $totalRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->count();
        $admissionDone = Student::where('school_id', $this->getSchoolId())->count();
        $pendingRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->where('admission_status', 'Pending')->count();
        $cancelledRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->where('admission_status', 'Cancelled')->count();
        $totalEnquiry = \App\Models\StudentEnquiry::where('school_id', $this->getSchoolId())->count();

        return view('receptionist.admission.index', compact(
            'students', 
            'totalRegistration', 
            'admissionDone', 
            'pendingRegistration', 
            'cancelledRegistration',
            'totalEnquiry'
        ));
    }

    public function create()
    {
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();
        
        // Fetch student registrations with pending status
        $registrations = StudentRegistration::where('school_id', $this->getSchoolId())
            ->where('admission_status', 'Pending')
            ->get();
        
        // Fetch master data
        $bloodGroups = \App\Models\BloodGroup::where('school_id', $this->getSchoolId())->get();
        $religions = \App\Models\Religion::where('school_id', $this->getSchoolId())->get();
        $categories = \App\Models\Category::where('school_id', $this->getSchoolId())->get();
        $studentTypes = \App\Models\StudentType::where('school_id', $this->getSchoolId())->get();
        $correspondingRelatives = \App\Models\CorrespondingRelative::where('school_id', $this->getSchoolId())->get();
        $qualifications = \App\Models\Qualification::where('school_id', $this->getSchoolId())->get();
        
        // Generate next admission number
        $lastStudent = Student::where('school_id', $this->getSchoolId())->latest()->first();
        $nextAdmissionNo = $lastStudent ? (intval($lastStudent->admission_no) + 1) : 100001;

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
            'qualifications'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            // Add other validations as needed
        ]);

        DB::beginTransaction();
        try {
            $student = new Student();
            $student->school_id = $this->getSchoolId();
            $student->user_id = Auth::id(); // Ideally create a new user for student
            $student->fill($request->all());
            
            // Generate Admission No if not provided
            if (!$request->admission_no) {
                $lastStudent = Student::where('school_id', $this->getSchoolId())->latest()->first();
                $student->admission_no = $lastStudent ? (intval($lastStudent->admission_no) + 1) : 100001;
            }

            $student->save();

            // If linked to a registration, update its status
            if ($request->registration_no) {
                $registration = StudentRegistration::where('registration_no', $request->registration_no)->first();
                if ($registration) {
                    $registration->update(['admission_status' => 'Admitted']);
                }
            }

            DB::commit();
            return redirect()->route('receptionist.admission.index')->with('success', 'Student admitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error admitting student: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Student $student)
    {
        $this->authorizeTenant($student);
        return view('receptionist.admission.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $this->authorizeTenant($student);
        
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();

        return view('receptionist.admission.edit', compact('student', 'classes', 'sections', 'academicYears'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorizeTenant($student);
        
        // Validation and update logic
        $student->update($request->all());
        return redirect()->route('receptionist.admission.index')->with('success', 'Student details updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->authorizeTenant($student);
        
        $student->delete();
        return redirect()->route('receptionist.admission.index')->with('success', 'Student record deleted successfully.');
    }

    /**
     * Get sections and admission fee for a specific class
     */
    public function getClassData($classId)
    {
        $sections = Section::where('class_id', $classId)
            ->where('school_id', $this->getSchoolId())
            ->get(['id', 'name']);

        $admissionFee = \App\Models\AdmissionFee::where('class_id', $classId)
            ->where('school_id', $this->getSchoolId())
            ->first();

        return response()->json([
            'sections' => $sections,
            'admission_fee' => $admissionFee ? $admissionFee->amount : 0
        ]);
    }
}
