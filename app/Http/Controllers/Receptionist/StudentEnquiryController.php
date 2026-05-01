<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\StudentEnquiry;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Enums\EnquiryStatus;
use App\Enums\Gender;
use App\Models\Religion;
use App\Models\Category;
use App\Models\Qualification;
use App\Models\BloodGroup;

use App\Traits\HasAjaxDataTable;

class StudentEnquiryController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();

        // 1. Row Transformer (Crucial for Gold Standard UI consistency)
        $transformer = function ($enquiry) {
            $status = $enquiry->form_status;
            
            // Map Tailwind-style config for badges
            // Defensively handle null or non-enum status to prevent 500 errors
            $color = ($status instanceof EnquiryStatus) ? $status->color() : 'gray';
            
            $statusConfig = [
                'bg' => "bg-{$color}-50",
                'text' => "text-{$color}-700",
                'border' => "border-{$color}-100",
                'icon' => match ($status) {
                    EnquiryStatus::Pending => 'fa-clock',
                    EnquiryStatus::Completed => 'fa-check-circle',
                    EnquiryStatus::Cancelled => 'fa-times-circle',
                    EnquiryStatus::Admitted => 'fa-user-check',
                    default => 'fa-question-circle'
                }
            ];

            return [
                'id' => $enquiry->id,
                'enquiry_no' => $enquiry->enquiry_no,
                'student_name' => $enquiry->student_name,
                'initials' => collect(explode(' ', $enquiry->student_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'father_name' => $enquiry->father_name,
                'contact_no' => $enquiry->contact_no,
                'class_name' => $enquiry->class?->name ?? 'N/A',
                'academic_year' => $enquiry->academicYear?->name ?? 'N/A',
                'status_label' => ($status instanceof EnquiryStatus) ? $status->label() : 'Pending',
                'status_config' => $statusConfig,
                'enquiry_date' => $enquiry->created_at->format('d M, Y'),
                'follow_up' => $enquiry->follow_up_date ? $enquiry->follow_up_date->format('d M, Y') : '--',
                'can_edit' => $status === EnquiryStatus::Pending,
            ];
        };

        // 2. Build Query
        $query = StudentEnquiry::where('school_id', $schoolId)
            ->with(['academicYear', 'class']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('enquiry_no', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('form_status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('follow_up_today')) {
            $query->whereDate('follow_up_date', today());
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

        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('receptionist.student-enquiries.index', [
            'initialData' => $initialData,
            'enquiries' => $initialData['rows'],
            'stats' => $initialData['stats'],
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    /**
     * Get aggregate statistics for the registry
     */
    private function getStats(int $schoolId): array
    {
        return [
            'total' => StudentEnquiry::where('school_id', $schoolId)->count(),
            'pending' => StudentEnquiry::where('school_id', $schoolId)->pending()->count(),
            'cancelled' => StudentEnquiry::where('school_id', $schoolId)->cancelled()->count(),
            'registration' => StudentEnquiry::where('school_id', $schoolId)->completed()->count(),
            'admitted' => StudentEnquiry::where('school_id', $schoolId)->admitted()->count(),
        ];
    }


    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_enquiries_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Enquiry No', 'Student Name', 'Father Name', 'Contact', 'Class', 'Status', 'Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function ($enq) use ($file) {
                fputcsv($file, [
                    $enq->enquiry_no,
                    $enq->student_name,
                    $enq->father_name,
                    $enq->contact_no,
                    $enq->class?->name ?? 'N/A',
                    $enq->form_status?->label() ?? 'Pending',
                    $enq->created_at->format('Y-m-d')
                ]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $countries = Country::all();
        $religions = Religion::where('school_id', $schoolId)->get();
        $categories = Category::where('school_id', $schoolId)->get();
        $qualifications = Qualification::where('school_id', $schoolId)->get();
        $bloodGroups = BloodGroup::where('school_id', $schoolId)->get();

        return view('receptionist.student-enquiries.create', compact('academicYears', 'classes', 'countries', 'religions', 'categories', 'qualifications', 'bloodGroups'));
    }

    public function store(Request $request)
    {
        $this->authorize('receptionist:operate');

        try {
            $validated = $this->validateEnquiry($request);

            $schoolId = $this->getSchoolId();
            $validated['school_id'] = $schoolId;

            // Handle file uploads
            $validated = $this->handleFileUploads($request, $validated);

            StudentEnquiry::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enquiry added successfully.',
                    'redirect' => route('receptionist.student-enquiries.index')
                ]);
            }

            return redirect()->route('receptionist.student-enquiries.index')
                ->with('success', 'Student enquiry added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operational failure: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Operational failure: ' . $e->getMessage());
        }
    }

    public function show(StudentEnquiry $studentEnquiry)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($studentEnquiry);
        return view('receptionist.student-enquiries.show', compact('studentEnquiry'));
    }

    public function edit(StudentEnquiry $studentEnquiry)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($studentEnquiry);

        $schoolId = $this->getSchoolId();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $countries = Country::all();
        $religions = Religion::where('school_id', $schoolId)->get();
        $categories = Category::where('school_id', $schoolId)->get();
        $qualifications = Qualification::where('school_id', $schoolId)->get();
        $bloodGroups = BloodGroup::where('school_id', $schoolId)->get();

        return view('receptionist.student-enquiries.edit', compact('studentEnquiry', 'academicYears', 'classes', 'countries', 'religions', 'categories', 'qualifications', 'bloodGroups'));
    }

    public function update(Request $request, StudentEnquiry $studentEnquiry)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($studentEnquiry);

        try {
            $validated = $this->validateEnquiry($request, $studentEnquiry->id);

            // Handle file uploads
            $validated = $this->handleFileUploads($request, $validated, $studentEnquiry);

            $studentEnquiry->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enquiry updated successfully.',
                    'redirect' => route('receptionist.student-enquiries.index')
                ]);
            }

            return redirect()->route('receptionist.student-enquiries.index')
                ->with('success', 'Student enquiry updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operational failure: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Operational failure: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, StudentEnquiry $studentEnquiry)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($studentEnquiry);

        $validated = $request->validate([
            'form_status' => ['required', 'integer', Rule::enum(EnquiryStatus::class)],
        ]);

        if ($studentEnquiry->form_status === EnquiryStatus::Admitted) {
            return response()->json(['success' => false, 'message' => 'Cannot change status of an admitted enquiry.'], 422);
        }

        $studentEnquiry->update(['form_status' => $validated['form_status']]);
        $newStatus = EnquiryStatus::from($validated['form_status']);
        $color = $newStatus->color();

        if ($request->ajax() || $request->wantsJson() || $request->isMethod('patch')) {
            return response()->json([
                'success'      => true,
                'message'      => 'Status updated to ' . $newStatus->label(),
                'status_label' => $newStatus->label(),
                'status_config' => [
                    'bg'     => "bg-{$color}-50",
                    'text'   => "text-{$color}-700",
                    'border' => "border-{$color}-100",
                    'icon'   => match($newStatus) {
                        EnquiryStatus::Pending   => 'fa-clock',
                        EnquiryStatus::Completed => 'fa-check-circle',
                        EnquiryStatus::Cancelled => 'fa-times-circle',
                        EnquiryStatus::Admitted  => 'fa-user-check',
                    },
                ],
            ]);
        }

        return back()->with('success', 'Status updated to ' . $newStatus->label());
    }

    public function destroy(StudentEnquiry $studentEnquiry)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeTenant($studentEnquiry);

        // Block deletion if enquiry has progressed to registration or admission
        if (in_array($studentEnquiry->form_status, [EnquiryStatus::Completed, EnquiryStatus::Admitted])) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete an enquiry that has been converted to a registration or admission.'], 422);
            }
            return redirect()->route('receptionist.student-enquiries.index')
                ->with('error', 'Cannot delete an enquiry that has been converted to a registration or admission.');
        }

        $this->deleteFiles($studentEnquiry);
        $studentEnquiry->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Student enquiry deleted successfully.']);
        }

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
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId())
            ],
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())
            ],

            'subject_name' => 'nullable|string|max:255',
            'student_name' => 'required|string|max:255',
            'gender' => ['nullable', 'integer', Rule::enum(Gender::class)],
            'follow_up_date' => 'nullable|date',

            // Father's Details
            'father_name' => 'required|string|max:255',
            'father_contact' => 'required|string|max:20',
            'father_email' => 'nullable|email|max:255',
            'father_qualification_id' => [
                'nullable',
                Rule::exists('qualifications', 'id')->where('school_id', $this->getSchoolId())
            ],
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
            'mother_qualification_id' => [
                'nullable',
                Rule::exists('qualifications', 'id')->where('school_id', $this->getSchoolId())
            ],
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
            'aadhaar_no' => 'nullable|string|max:12',
            'grand_father_name' => 'nullable|string|max:255',
            'annual_income' => 'nullable|numeric|min:0',
            'no_of_brothers' => 'nullable|integer|min:0',
            'no_of_sisters' => 'nullable|integer|min:0',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('school_id', $this->getSchoolId())
            ],
            'minority' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\YesNo::class)],
            'blood_group_id' => [
                'nullable',
                Rule::exists('blood_groups', 'id')->where('school_id', $this->getSchoolId())
            ],
            'religion_id' => [
                'nullable',
                Rule::exists('religions', 'id')->where('school_id', $this->getSchoolId())
            ],
            'student_type_id' => [
                'nullable',
                Rule::exists('student_types', 'id')->where('school_id', $this->getSchoolId())
            ],
            'corresponding_relative_id' => [
                'nullable',
                Rule::exists('corresponding_relatives', 'id')->where('school_id', $this->getSchoolId())
            ],
            'boarding_type_id' => [
                'nullable',
                Rule::exists('boarding_types', 'id')->where('school_id', $this->getSchoolId())
            ],
            'transport_facility' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\YesNo::class)],
            'hostel_facility' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\YesNo::class)],
            'previous_class' => 'nullable|string|max:255',
            'identity_marks' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
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
