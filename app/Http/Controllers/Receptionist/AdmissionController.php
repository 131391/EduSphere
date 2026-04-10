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
use App\Enums\FeeStatus;
use App\Http\Requests\School\StoreAdmissionRequest;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeePayment;
use App\Models\StudentTransportAssignment;
use App\Models\HostelBedAssignment;
use App\Traits\HandlesFileCopies;
use App\Enums\GeneralStatus;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionController extends TenantController
{
    use HandlesFileCopies;
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
        
        // Fetch student registrations for dropdown
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
        $transportRoutes = \App\Models\TransportRoute::where('school_id', $this->getSchoolId())->get();
        $hostels = \App\Models\Hostel::where('school_id', $this->getSchoolId())->get();
        
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
            'qualifications',
            'transportRoutes',
            'hostels'
        ));
    }


    public function store(StoreAdmissionRequest $request)
    {
        $school = $this->getSchool();
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $student = new Student();
            $student->school_id = $school->id;
            
            // Generate Admission No early if not provided to use for User generation
            $admissionNo = $request->admission_no;
            if (!$admissionNo) {
                $lastStudent = Student::where('school_id', $school->id)->latest()->first();
                $admissionNo = $lastStudent ? (intval($lastStudent->admission_no) + 1) : 100001;
            }
            $student->admission_no = $admissionNo;

            // Create Student User Account
            $studentRole = \App\Models\Role::where('slug', \App\Models\Role::STUDENT)->first();
            $userEmail = $request->email ?: 'student.' . $admissionNo . '@edusphere.local';
            
            $user = \App\Models\User::create([
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $userEmail,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role_id' => $studentRole ? $studentRole->id : null,
                'school_id' => $school->id,
                'phone' => $request->phone,
                'status' => \App\Enums\UserStatus::Active,
            ]);
            
            $student->user_id = $user->id;
            
            // Exclude non-model fields
            $excludedFields = [
                'student_photo', 'father_photo', 'mother_photo',
                'student_signature', 'father_signature', 'mother_signature',
                'registration_id', 'student_photo_path', 'father_photo_path', 'mother_photo_path',
                'student_signature_path', 'father_signature_path', 'mother_signature_path',
                'admission_fee', 'transport_route_id', 'hostel_id'
            ];
            
            $student->fill($request->except($excludedFields));
            
            // Handle Photo/Signature copies or uploads using Trait
            $student->photo = $this->storeTenantFile($request->file('student_photo'), "students/{$school->id}/photos", $request->student_photo_path);
            $student->father_photo = $this->storeTenantFile($request->file('father_photo'), "parents/{$school->id}/photos", $request->father_photo_path);
            $student->mother_photo = $this->storeTenantFile($request->file('mother_photo'), "parents/{$school->id}/photos", $request->mother_photo_path);
            
            // Admission No is already generated above

            // Concatenate Parent Names if necessary (handled by validation mapping usually but ensuring here)
            if (!$student->father_name) {
                $student->father_name = trim($request->father_first_name . ' ' . ($request->father_middle_name ?? '') . ' ' . $request->father_last_name);
            }
            if (!$student->mother_name) {
                $student->mother_name = trim($request->mother_first_name . ' ' . ($request->mother_middle_name ?? '') . ' ' . $request->mother_last_name);
            }

            $student->save();

            // --- FINANCIAL INTEGRATION ---
            // 1. Find the 'Admission Fee' name for this school
            $admissionFeeName = FeeName::where('school_id', $school->id)
                ->where('name', 'Admission Fee')
                ->first();

            if ($admissionFeeName) {
                // 2. Create the Fee Record (Ledger)
                $fee = Fee::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'academic_year_id' => $student->academic_year_id,
                    'fee_type_id' => $admissionFeeName->fee_type_id,
                    'fee_name_id' => $admissionFeeName->id,
                    'class_id' => $student->class_id,
                    'bill_no' => 'ADM-' . time(),
                    'fee_period' => 'Admission',
                    'payable_amount' => $request->admission_fee,
                    'paid_amount' => $request->admission_fee,
                    'due_amount' => 0,
                    'due_date' => now(),
                    'payment_date' => now(),
                    'payment_status' => \App\Enums\FeeStatus::Paid,
                    'payment_mode' => 'Cash', // Default for now
                    'remarks' => 'Admission Fee paid during intake'
                ]);

                // 3. Create the Fee Payment Record (Transaction)
                $cashPaymentMethod = \App\Models\PaymentMethod::where('name', 'Cash')->first();

                FeePayment::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'fee_id' => $fee->id,
                    'academic_year_id' => $student->academic_year_id,
                    'amount' => $request->admission_fee,
                    'payment_date' => now(),
                    'payment_method_id' => $cashPaymentMethod->id ?? 1,
                    'receipt_no' => $request->receipt_no,
                    'created_by' => Auth::id(),
                ]);
            }

            // --- FACILITY INTEGRATION (PHASE 2 PREP) ---
            if ($request->transport_route_id) {
                StudentTransportAssignment::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'transport_route_id' => $request->transport_route_id,
                    'status' => GeneralStatus::Active,
                    'start_date' => now()
                ]);
            }

            if ($request->hostel_id) {
                // Simplified assignment - Phase 2 will handle floor/room selection
                HostelBedAssignment::create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'hostel_id' => $request->hostel_id,
                    'status' => GeneralStatus::Active,
                    'start_date' => now()
                ]);
            }

            // If linked to a registration, update its status and the underlying Enquiry
            if ($request->registration_no) {
                $registration = StudentRegistration::where('registration_no', $request->registration_no)
                    ->where('school_id', $school->id)
                    ->first();
                if ($registration) {
                    $registration->update(['admission_status' => AdmissionStatus::Admitted]);
                    
                    // Transition Registration Fees and Payments to this student's ledger
                    $registrationFees = Fee::where('school_id', $school->id)
                        ->where('registration_id', $registration->id)
                        ->whereNull('student_id')
                        ->pluck('id');

                    if ($registrationFees->isNotEmpty()) {
                        Fee::whereIn('id', $registrationFees)->update(['student_id' => $student->id]);
                        FeePayment::whereIn('fee_id', $registrationFees)->whereNull('student_id')->update(['student_id' => $student->id]);
                    }

                    if ($registration->enquiry_id) {
                        $enquiry = \App\Models\StudentEnquiry::find($registration->enquiry_id);
                        if ($enquiry) {
                            $enquiry->update(['form_status' => \App\Enums\EnquiryStatus::Admitted]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('receptionist.admission.index')->with('success', 'Student admitted successfully and fee records generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Admission Error: " . $e->getMessage());
            return back()->with('error', 'Error admitting student: ' . $e->getMessage())->withInput();
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
        $transportRoutes = \App\Models\TransportRoute::where('school_id', $this->getSchoolId())->get();
        $hostels = \App\Models\Hostel::where('school_id', $this->getSchoolId())->get();

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
            'hostels'
        ));
    }

    public function update(Request $request, Student $student)
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
            'permanent_country_id' => 'required|exists:countries,id',
            'permanent_state_id' => 'required|exists:states,id',
            'permanent_city_id' => 'required|exists:cities,id',
            'correspondence_address' => 'required|string',
            'correspondence_country_id' => 'nullable|exists:countries,id',
            'correspondence_state_id' => 'nullable|exists:states,id',
            'correspondence_city_id' => 'nullable|exists:cities,id',
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
            
            $student->fill($request->except($excludedFields));

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
                if ($student->signature) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($student->signature);
                }
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
            return redirect()->route('receptionist.admission.index')->with('success', 'Student details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating student: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $student->forceDelete();
        return redirect()->route('receptionist.admission.index')->with('success', 'Student record deleted successfully.');
    }

    public function getClassData($classId)
    {
        $sections = Section::where('school_id', $this->getSchoolId())
            ->where('class_id', $classId)
            ->get(['id', 'name']);
        
        $admissionFee = \App\Models\AdmissionFee::where('school_id', $this->getSchoolId())
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
