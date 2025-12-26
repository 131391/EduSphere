<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Staff;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Enums\Gender;
use App\Enums\StaffPost;

class StaffController extends TenantController
{
    /**
     * Display a listing of staff.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = Staff::where('school_id', $schoolId)
            ->with(['class', 'section', 'higherQualification']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('aadhar_no', 'like', "%{$search}%");
            });
        }

        // Filter by post
        if ($request->filled('post')) {
            $query->where('post', $request->post);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $staff = $query->paginate($perPage)->withQueryString();

        // Get classes and qualifications for filters
        $classes = ClassModel::where('school_id', $schoolId)->orderBy('order')->get();
        $qualifications = Qualification::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('receptionist.staff.index', compact('staff', 'classes', 'qualifications'));
    }

    /**
     * Store a newly created staff.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post' => ['required', 'integer', Rule::enum(StaffPost::class)],
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => ['nullable', 'integer', Rule::enum(Gender::class)],
            'total_experience' => 'nullable|integer|min:0',
            'previous_school_salary' => 'nullable|numeric|min:0',
            'current_salary' => 'nullable|numeric|min:0',
            'country_id' => 'nullable|integer',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'aadhar_no' => 'nullable|string|max:20',
            'aadhar_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'staff_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'joining_date' => 'nullable|date',
            'higher_qualification_id' => 'nullable|exists:qualifications,id',
            'previous_school_company_name' => 'nullable|string|max:255',
        ]);

        $schoolId = $this->getSchoolId();
        $validated['school_id'] = $schoolId;

        // Handle file uploads
        if ($request->hasFile('aadhar_card')) {
            $validated['aadhar_card'] = $request->file('aadhar_card')->store('staff/aadhar', 'public');
        }

        if ($request->hasFile('staff_image')) {
            $validated['staff_image'] = $request->file('staff_image')->store('staff/images', 'public');
        }

        try {
            Staff::create($validated);
            return redirect()->route('receptionist.staff.index')->with('success', 'Staff added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create staff. Please try again.'])->withInput();
        }
    }

    /**
     * Update the specified staff.
     */
    public function update(Request $request, Staff $staff)
    {
        $this->authorizeTenant($staff);

        $validated = $request->validate([
            'post' => ['required', 'integer', Rule::enum(StaffPost::class)],
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => ['nullable', 'integer', Rule::enum(Gender::class)],
            'total_experience' => 'nullable|integer|min:0',
            'previous_school_salary' => 'nullable|numeric|min:0',
            'current_salary' => 'nullable|numeric|min:0',
            'country_id' => 'nullable|integer',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'aadhar_no' => 'nullable|string|max:20',
            'aadhar_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'staff_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'joining_date' => 'nullable|date',
            'higher_qualification_id' => 'nullable|exists:qualifications,id',
            'previous_school_company_name' => 'nullable|string|max:255',
        ]);

        // Handle file uploads
        if ($request->hasFile('aadhar_card')) {
            // Delete old file if exists
            if ($staff->aadhar_card && Storage::disk('public')->exists($staff->aadhar_card)) {
                Storage::disk('public')->delete($staff->aadhar_card);
            }
            $validated['aadhar_card'] = $request->file('aadhar_card')->store('staff/aadhar', 'public');
        }

        if ($request->hasFile('staff_image')) {
            // Delete old file if exists
            if ($staff->staff_image && Storage::disk('public')->exists($staff->staff_image)) {
                Storage::disk('public')->delete($staff->staff_image);
            }
            $validated['staff_image'] = $request->file('staff_image')->store('staff/images', 'public');
        }

        try {
            $staff->update($validated);
            return redirect()->route('receptionist.staff.index')->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update staff. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified staff.
     */
    public function destroy(Staff $staff)
    {
        $this->authorizeTenant($staff);

        // Delete files if they exist
        if ($staff->aadhar_card && Storage::disk('public')->exists($staff->aadhar_card)) {
            Storage::disk('public')->delete($staff->aadhar_card);
        }
        if ($staff->staff_image && Storage::disk('public')->exists($staff->staff_image)) {
            Storage::disk('public')->delete($staff->staff_image);
        }

        $staff->delete();

        return redirect()->route('receptionist.staff.index')->with('success', 'Staff deleted successfully.');
    }

    /**
     * Get sections for a class (AJAX endpoint).
     */
    public function getSections(Request $request, $classId)
    {
        $schoolId = $this->getSchoolId();
        
        $sections = Section::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['sections' => $sections]);
    }
}
