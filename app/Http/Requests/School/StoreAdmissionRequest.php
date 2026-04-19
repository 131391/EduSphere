<?php

namespace App\Http\Requests\School;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $schoolId = Auth::user()->school_id;

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('school_id', $schoolId)
            ],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where('school_id', $schoolId)
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId)
            ],
            'admission_date' => 'required|date',
            'dob' => 'required|date',
            'gender' => ['required', 'integer', Rule::enum(Gender::class)],
            'aadhaar_no' => 'nullable|string|max:12',
            'father_aadhaar_no' => 'nullable|string|max:12',
            'mother_aadhaar_no' => 'nullable|string|max:12',

            // Master Data IDs
            'blood_group_id'           => ['nullable', Rule::exists('blood_groups', 'id')->where('school_id', $schoolId)],
            'religion_id'              => ['nullable', Rule::exists('religions', 'id')->where('school_id', $schoolId)],
            'category_id'              => ['nullable', Rule::exists('categories', 'id')->where('school_id', $schoolId)],
            'student_type_id'          => ['nullable', Rule::exists('student_types', 'id')->where('school_id', $schoolId)],
            'corresponding_relative_id'=> ['nullable', Rule::exists('corresponding_relatives', 'id')->where('school_id', $schoolId)],
            'boarding_type_id'         => ['nullable', Rule::exists('boarding_types', 'id')->where('school_id', $schoolId)],
            'father_qualification_id'  => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $schoolId)],
            'mother_qualification_id'  => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $schoolId)],
            'permanent_address' => 'required|string',
            'permanent_country_id' => 'required|exists:countries,id',
            'permanent_state_id' => 'required|exists:states,id',
            'permanent_city_id' => 'required|exists:cities,id',
            'correspondence_address' => 'nullable|string',
            'correspondence_country_id' => 'nullable|exists:countries,id',
            'correspondence_state_id' => 'nullable|exists:states,id',
            'correspondence_city_id' => 'nullable|exists:cities,id',
            
            // Father's Details
            'father_first_name' => 'required|string|max:255',
            'father_last_name' => 'required|string|max:255',
            'father_mobile_no' => 'required|string|max:20',
            
            // Mother's Details
            'mother_first_name' => 'required|string|max:255',
            'mother_last_name' => 'required|string|max:255',
            'mother_mobile_no' => 'required|string|max:20',
            
            // Admission Details
            'roll_no' => 'required|string|max:255',
            'receipt_no' => 'required|string|max:255',
            'admission_fee' => 'required|numeric|min:0',
            
            // Photos
            'student_photo' => 'nullable|image|max:2048',
            'father_photo' => 'nullable|image|max:2048',
            'mother_photo' => 'nullable|image|max:2048',
            
            // Reference Data — registration is optional (supports walk-in / data-migration admissions)
            'registration_no' => [
                'nullable',
                Rule::exists('student_registrations', 'registration_no')->where('school_id', $schoolId)
            ],
            
            // Facility Selection (Phase 2 Prep)
            'transport_route_id' => [
                'nullable',
                Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)
            ],
            'hostel_id' => [
                'nullable',
                Rule::exists('hostels', 'id')->where('school_id', $schoolId)
            ],
            // hostel_room_id must belong to the selected hostel AND this school
            'hostel_room_id' => [
                'nullable',
                'required_with:hostel_id',
                Rule::exists('hostel_rooms', 'id')->where('school_id', $schoolId)
            ],
            // bed_no is a string field on hostel_bed_assignments, not a separate table
            'hostel_bed_no' => ['nullable', 'string', 'max:50'],

            // Payment method for admission fee
            'admission_payment_method_id' => [
                'nullable',
                Rule::exists('payment_methods', 'id')->where('school_id', $schoolId)
            ],
        ];
    }
}
