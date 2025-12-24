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
use Illuminate\Validation\Rule;
use App\Enums\AdmissionStatus;
use App\Enums\Gender;

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

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students = $query->latest()->paginate(10);
        
        // Stats
        $totalRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->count();
        $admissionDone = Student::where('school_id', $this->getSchoolId())->count();
        $pendingRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->pending()->count();
        $cancelledRegistration = StudentRegistration::where('school_id', $this->getSchoolId())->cancelled()->count();
        $totalEnquiry = \App\Models\StudentEnquiry::where('school_id', $this->getSchoolId())->count();

        $classes = \App\Models\ClassModel::where('school_id', $this->getSchoolId())->get();

        return view('receptionist.admission.index', compact(
            'students', 
            'totalRegistration', 
            'admissionDone', 
            'pendingRegistration', 
            'cancelledRegistration',
            'totalEnquiry',
            'classes'
        ));
    }

    public function create()
    {
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();
        
        // Fetch student registrations with pending status
        $registrations = StudentRegistration::where('school_id', $this->getSchoolId())
            ->pending()
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
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'required|date',
            'gender' => ['required', 'integer', Rule::enum(Gender::class)],
            'permanent_address' => 'required|string',
            'correspondence_address' => 'required|string',
            'father_first_name' => 'required|string|max:255',
            'father_last_name' => 'required|string|max:255',
            'father_mobile' => 'required|string|max:20',
            'mother_first_name' => 'required|string|max:255',
            'mother_last_name' => 'required|string|max:255',
            'mother_mobile' => 'required|string|max:20',
            'roll_no' => 'required|string|max:255',
            'receipt_no' => 'required|string|max:255',
            'admission_fee' => 'required|numeric|min:0',
            'student_photo' => 'nullable|image|max:2048',
            // Add other validations as needed
        ]);

        DB::beginTransaction();
        try {
            $student = new Student();
            $student->school_id = $this->getSchoolId();
            $student->user_id = Auth::id(); // Ideally create a new user for student
            $student->fill($request->except(['student_photo', 'father_photo', 'mother_photo']));
            
            // Handle Student Photo
            if ($request->hasFile('student_photo')) {
                $path = $request->file('student_photo')->store('student_photos', 'public');
                $student->photo = $path;
            } elseif ($request->filled('student_photo_path')) {
                $sourcePath = $request->student_photo_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'student_photos/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->photo = $newPath;
                }
            }

            // Handle Father Photo
            if ($request->hasFile('father_photo')) {
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
                $path = $request->file('student_signature')->store('student_signatures', 'public');
                $student->signature = $path;
            } elseif ($request->filled('student_signature_path')) {
                $sourcePath = $request->student_signature_path;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $newPath = 'student_signatures/' . Str::random(40) . '.' . $extension;
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($sourcePath, $newPath);
                    $student->signature = $newPath;
                }
            }

            // Handle Father Signature
            if ($request->hasFile('father_signature')) {
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
            
            // Concatenate Father Name
            $fatherName = trim($request->father_first_name . ' ' . $request->father_middle_name . ' ' . $request->father_last_name);
            $student->father_name = $fatherName;

            // Concatenate Mother Name
            $motherName = trim($request->mother_first_name . ' ' . $request->mother_middle_name . ' ' . $request->mother_last_name);
            $student->mother_name = $motherName;
            
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
                    $registration->update(['admission_status' => AdmissionStatus::Admitted]);
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

        // Fetch student registrations (Pending + current student's registration)
        $registrations = StudentRegistration::where('school_id', $this->getSchoolId())
            ->where(function($query) use ($student) {
                $query->where('admission_status', AdmissionStatus::Pending);
                if ($student->registration_no) {
                    $query->orWhere('registration_no', $student->registration_no);
                }
            })
            ->get();
        
        // Fetch master data
        $bloodGroups = \App\Models\BloodGroup::where('school_id', $this->getSchoolId())->get();
        $religions = \App\Models\Religion::where('school_id', $this->getSchoolId())->get();
        $categories = \App\Models\Category::where('school_id', $this->getSchoolId())->get();
        $studentTypes = \App\Models\StudentType::where('school_id', $this->getSchoolId())->get();
        $correspondingRelatives = \App\Models\CorrespondingRelative::where('school_id', $this->getSchoolId())->get();
        $qualifications = \App\Models\Qualification::where('school_id', $this->getSchoolId())->get();

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
            'qualifications'
        ));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorizeTenant($student);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'required|date',
            'gender' => ['required', 'integer', Rule::enum(Gender::class)],
            'permanent_address' => 'required|string',
            'correspondence_address' => 'required|string',
            'father_first_name' => 'required|string|max:255',
            'father_last_name' => 'required|string|max:255',
            'father_mobile' => 'required|string|max:20',
            'mother_first_name' => 'required|string|max:255',
            'mother_last_name' => 'required|string|max:255',
            'mother_mobile' => 'required|string|max:20',
            'roll_no' => 'required|string|max:255',
            'receipt_no' => 'required|string|max:255',
            'admission_fee' => 'required|numeric|min:0',
            'student_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $student->fill($request->except(['student_photo', 'father_photo', 'mother_photo']));

            // Concatenate Father Name
            $fatherName = trim($request->father_first_name . ' ' . $request->father_middle_name . ' ' . $request->father_last_name);
            $student->father_name = $fatherName;

            // Concatenate Mother Name
            $motherName = trim($request->mother_first_name . ' ' . $request->mother_middle_name . ' ' . $request->mother_last_name);
            $student->mother_name = $motherName;

            // Handle Student Photo
            if ($request->hasFile('student_photo')) {
                if ($student->photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
                }
                $path = $request->file('student_photo')->store('student_photos', 'public');
                $student->photo = $path;
            }

            // Handle Father Photo
            if ($request->hasFile('father_photo')) {
                if ($student->father_photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->father_photo);
                }
                $path = $request->file('father_photo')->store('parent_photos', 'public');
                $student->father_photo = $path;
            }

            // Handle Mother Photo
            if ($request->hasFile('mother_photo')) {
                if ($student->mother_photo) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->mother_photo);
                }
                $path = $request->file('mother_photo')->store('parent_photos', 'public');
                $student->mother_photo = $path;
            }

            // Handle Student Signature
            if ($request->hasFile('student_signature')) {
                if ($student->signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->signature);
                }
                $path = $request->file('student_signature')->store('student_signatures', 'public');
                $student->signature = $path;
            }

            // Handle Father Signature
            if ($request->hasFile('father_signature')) {
                if ($student->father_signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->father_signature);
                }
                $path = $request->file('father_signature')->store('parent_signatures', 'public');
                $student->father_signature = $path;
            }

            // Handle Mother Signature
            if ($request->hasFile('mother_signature')) {
                if ($student->mother_signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->mother_signature);
                }
                $path = $request->file('mother_signature')->store('parent_signatures', 'public');
                $student->mother_signature = $path;
            }

            $student->save();

            DB::commit();
            return redirect()->route('receptionist.admission.index')->with('success', 'Student details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating student: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $this->authorizeTenant($student);
        
        $student->forceDelete();
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
            $data['dob'] = $registration->dob->format('Y-m-d');
        }
        if ($registration->registration_date) {
            $data['registration_date'] = $registration->registration_date->format('Y-m-d');
        }
        
        return response()->json($data);
    }

    public function downloadPdf($id)
    {
        $school = $this->getSchool();
        
        $student = Student::with(['class', 'section', 'academicYear'])
            ->where('school_id', $this->getSchoolId())
            ->findOrFail($id);
        
        $pdf = \PDF::loadView('pdf.student-admission', compact('student', 'school'));
        
        return $pdf->download('student-admission-' . $student->admission_no . '.pdf');
    }
}
