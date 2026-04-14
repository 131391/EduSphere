<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\StudentEnquiry;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Enums\EnquiryStatus;
use App\Enums\Gender;

class StudentEnquiryController extends TenantController
{
    public function index(Request $request)
    {
        $school = $this->getSchool();
        
        $query = StudentEnquiry::where('school_id', $this->getSchoolId())
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
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $enquiries = $query->paginate($perPage)->withQueryString();

        // Statistics - Scoped to school
        $stats = [
            'total' => StudentEnquiry::where('school_id', $this->getSchoolId())->count(),
            'pending' => StudentEnquiry::where('school_id', $this->getSchoolId())->pending()->count(),
            'cancelled' => StudentEnquiry::where('school_id', $this->getSchoolId())->cancelled()->count(),
            'registration' => StudentEnquiry::where('school_id', $this->getSchoolId())->completed()->count(),
            'admitted' => StudentEnquiry::where('school_id', $this->getSchoolId())->admitted()->count(),
        ];

        // Get academic years and classes for dropdowns
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $classes = ClassModel::where('school_id', $school->id)->get();

        return view('school.student-enquiries.index', compact('enquiries', 'stats', 'academicYears', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEnquiry($request);
        $validated['school_id'] = $this->getSchoolId();

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $validated);

        try {
            StudentEnquiry::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enquiry added successfully.'
                ]);
            }

            return redirect()->route('school.student-enquiries.index')
                ->with('success', 'Student enquiry added successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add enquiry: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to add enquiry: ' . $e->getMessage());
        }
    }

    public function update(Request $request, StudentEnquiry $studentEnquiry)
    {
        $this->authorizeTenant($studentEnquiry);

        $validated = $this->validateEnquiry($request, $studentEnquiry->id);

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $validated, $studentEnquiry);

        try {
            $studentEnquiry->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enquiry updated successfully.'
                ]);
            }

            return redirect()->route('school.student-enquiries.index')
                ->with('success', 'Student enquiry updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update enquiry: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update enquiry: ' . $e->getMessage());
        }
    }

    public function destroy(StudentEnquiry $studentEnquiry)
    {
        $this->authorizeTenant($studentEnquiry);

        // Delete associated files
        $this->deleteFiles($studentEnquiry);

        try {
            // Delete associated files
            $this->deleteFiles($studentEnquiry);

            $studentEnquiry->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enquiry deleted successfully.'
                ]);
            }

            return redirect()->route('school.student-enquiries.index')
                ->with('success', 'Student enquiry deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete enquiry: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('school.student-enquiries.index')
                ->with('error', 'Failed to delete enquiry: ' . $e->getMessage());
        }
    }

    /**
     * Validation rules
     */
    private function validateEnquiry(Request $request, $id = null)
    {
        return $request->validate([
            // Enquiry Form
            'academic_year_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())
            ],
            'class_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())
            ],

            'subject_name' => 'nullable|string|max:255',
            'student_name' => 'required|string|max:255',
            'gender' => ['nullable', 'integer', Rule::enum(Gender::class)],
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
            'form_status' => ['nullable', 'integer', Rule::enum(EnquiryStatus::class)],
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
