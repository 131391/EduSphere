<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\StudentEnquiry;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentEnquiryController extends TenantController
{
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = StudentEnquiry::where('school_id', $schoolId)
            ->with(['academicYear', 'class']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('enquiry_no', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $enquiries = $query->paginate($perPage)->withQueryString();

        // Statistics
        $stats = [
            'total' => StudentEnquiry::where('school_id', $schoolId)->count(),
            'pending' => StudentEnquiry::where('school_id', $schoolId)->pending()->count(),
            'cancelled' => StudentEnquiry::where('school_id', $schoolId)->cancelled()->count(),
            'registration' => StudentEnquiry::where('school_id', $schoolId)->completed()->count(),
            'admitted' => StudentEnquiry::where('school_id', $schoolId)->admitted()->count(),
        ];

        // Get academic years and classes for dropdowns
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('receptionist.student-enquiries.index', compact('enquiries', 'stats', 'academicYears', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEnquiry($request);

        $validated['school_id'] = $this->getSchoolId();

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $validated);

        StudentEnquiry::create($validated);

        return redirect()->route('receptionist.student-enquiries.index')
            ->with('success', 'Student enquiry added successfully.');
    }

    public function update(Request $request, StudentEnquiry $studentEnquiry)
    {
        $this->authorizeTenant($studentEnquiry);

        $validated = $this->validateEnquiry($request, $studentEnquiry->id);

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $validated, $studentEnquiry);

        $studentEnquiry->update($validated);

        return redirect()->route('receptionist.student-enquiries.index')
            ->with('success', 'Student enquiry updated successfully.');
    }

    public function destroy(StudentEnquiry $studentEnquiry)
    {
        $this->authorizeTenant($studentEnquiry);

        // Delete associated files
        $this->deleteFiles($studentEnquiry);

        $studentEnquiry->delete();

        return redirect()->route('receptionist.student-enquiries.index')
            ->with('success', 'Student enquiry deleted successfully.');
    }

    /**
     * Validation rules
     */
    private function validateEnquiry(Request $request, $id = null)
    {
        return $request->validate([
            // Enquiry Form
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_name' => 'nullable|string|max:255',
            'student_name' => 'required|string|max:255',
            'gender' => 'nullable|in:Male,Female,Other',
            'follow_up_date' => 'nullable|date',
            
            // Father's Details
            'father_name' => 'required|string|max:255',
            'father_contact' => 'required|string|max:20',
            'father_email' => 'nullable|email|max:255',
            'father_qualification' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_annual_income' => 'nullable|numeric|min:0',
            'father_organization' => 'nullable|string|max:255',
            'father_office_address' => 'nullable|string',
            'father_department' => 'nullable|string|max:255',
            'father_designation' => 'nullable|string|max:255',
            
            // Mother's Details
            'mother_name' => 'required|string|max:255',
            'mother_contact' => 'required|string|max:20',
            'mother_email' => 'nullable|email|max:255',
            'mother_qualification' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_annual_income' => 'nullable|numeric|min:0',
            'mother_organization' => 'nullable|string|max:255',
            'mother_office_address' => 'nullable|string',
            'mother_department' => 'nullable|string|max:255',
            'mother_designation' => 'nullable|string|max:255',
            
            // Contact Details
            'contact_no' => 'required|string|max:20',
            'whatsapp_no' => 'required|string|max:20',
            'facebook_id' => 'nullable|string|max:255',
            'email_id' => 'nullable|email|max:255',
            'sms_no' => 'nullable|string|max:20',
            'twitter_id' => 'nullable|string|max:255',
            'emergency_contact_no' => 'nullable|string|max:20',
            
            // Personal Details
            'dob' => 'nullable|date',
            'aadhar_no' => 'nullable|string|max:12',
            'grand_father_name' => 'nullable|string|max:255',
            'annual_income' => 'nullable|numeric|min:0',
            'no_of_brothers' => 'nullable|integer|min:0',
            'no_of_sisters' => 'nullable|integer|min:0',
            'category' => 'nullable|in:General,OBC,SC,ST,Other',
            'minority' => 'nullable|in:Yes,No',
            'religion' => 'nullable|in:Hindu,Muslim,Christian,Sikh,Other',
            'transport_facility' => 'nullable|in:Yes,No',
            'hostel_facility' => 'nullable|in:Yes,No',
            'previous_class' => 'nullable|string|max:255',
            'identity_marks' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'country_id' => 'required|integer|min:1|max:65',
            'previous_school_name' => 'nullable|string|max:255',
            'student_roll_no' => 'nullable|string|max:50',
            'passing_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 20),
            'exam_name' => 'nullable|string|max:255',
            'board_university' => 'nullable|string|max:255',
            'only_child' => 'nullable|boolean',
            
            // Photos
            'father_photo' => 'nullable|image|max:2048',
            'mother_photo' => 'nullable|image|max:2048',
            'student_photo' => 'nullable|image|max:2048',
            
            // Status
            'form_status' => 'nullable|in:pending,completed,cancelled,admitted',
        ]);
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads(Request $request, array $validated, $enquiry = null)
    {
        if ($request->hasFile('father_photo')) {
            if ($enquiry && $enquiry->father_photo) {
                Storage::disk('public')->delete($enquiry->father_photo);
            }
            $validated['father_photo'] = $request->file('father_photo')->store('enquiries/photos', 'public');
        }

        if ($request->hasFile('mother_photo')) {
            if ($enquiry && $enquiry->mother_photo) {
                Storage::disk('public')->delete($enquiry->mother_photo);
            }
            $validated['mother_photo'] = $request->file('mother_photo')->store('enquiries/photos', 'public');
        }

        if ($request->hasFile('student_photo')) {
            if ($enquiry && $enquiry->student_photo) {
                Storage::disk('public')->delete($enquiry->student_photo);
            }
            $validated['student_photo'] = $request->file('student_photo')->store('enquiries/photos', 'public');
        }

        return $validated;
    }

    /**
     * Delete associated files
     */
    private function deleteFiles($enquiry)
    {
        if ($enquiry->father_photo) {
            Storage::disk('public')->delete($enquiry->father_photo);
        }
        if ($enquiry->mother_photo) {
            Storage::disk('public')->delete($enquiry->mother_photo);
        }
        if ($enquiry->student_photo) {
            Storage::disk('public')->delete($enquiry->student_photo);
        }
    }
}
