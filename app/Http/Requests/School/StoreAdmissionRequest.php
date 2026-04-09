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
            'phone' => 'required|string|max:20',
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
            'gender' => ['required', 'integer', Rule::enum(Gender::class)],
            'permanent_address' => 'required|string',
            'permanent_country_id' => 'required|exists:countries,id',
            'permanent_state_id' => 'required|exists:states,id',
            'permanent_city_id' => 'required|exists:cities,id',
            'correspondence_address' => 'required|string',
            'correspondence_country_id' => 'nullable|exists:countries,id',
            'correspondence_state_id' => 'nullable|exists:states,id',
            'correspondence_city_id' => 'nullable|exists:cities,id',
            
            // Father's Details
            'father_first_name' => 'required|string|max:255',
            'father_last_name' => 'required|string|max:255',
            'father_mobile' => 'required|string|max:20',
            
            // Mother's Details
            'mother_first_name' => 'required|string|max:255',
            'mother_last_name' => 'required|string|max:255',
            'mother_mobile' => 'required|string|max:20',
            
            // Admission Details
            'roll_no' => 'required|string|max:255',
            'receipt_no' => 'required|string|max:255',
            'admission_fee' => 'required|numeric|min:0',
            
            // Photos
            'student_photo' => 'nullable|image|max:2048',
            'father_photo' => 'nullable|image|max:2048',
            'mother_photo' => 'nullable|image|max:2048',
            
            // Reference Data
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
        ];
    }
}
