<?php

namespace App\Http\Requests\School;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * WHY a dedicated Form Request:
 * - Keeps AdmissionController::update() clean and single-responsibility.
 * - Centralises all tenant-scoped Rule::exists() checks in one place.
 * - Validates that section_id belongs to the chosen class_id (cross-field rule).
 * - Prevents a school admin from assigning a student to another school's class/section
 *   by scoping every foreign-key rule to the authenticated user's school_id.
 */
class UpdateAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $schoolId = Auth::user()->school_id;

        return [
            'first_name'               => 'required|string|max:255',
            'last_name'                => 'required|string|max:255',
            'mobile_no'                => 'required|string|max:20',

            // Tenant-scoped: class must belong to this school
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('school_id', $schoolId),
            ],

            // Tenant-scoped: section must belong to this school
            // Cross-field (section belongs to class) is enforced in withValidator() below
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where('school_id', $schoolId),
            ],

            // Tenant-scoped: academic year must belong to this school
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],

            'dob'                      => 'required|date',
            'admission_date'           => 'required|date',
            'gender'                   => ['required', 'integer', Rule::enum(Gender::class)],
            'aadhaar_no'               => 'nullable|digits:12',
            'father_aadhaar_no'        => 'nullable|digits:12',
            'mother_aadhaar_no'        => 'nullable|digits:12',

            // Parent / Master Data IDs (Normalized)
            'blood_group_id'           => ['nullable', Rule::exists('blood_groups', 'id')->where('school_id', $schoolId)],
            'religion_id'              => ['nullable', Rule::exists('religions', 'id')->where('school_id', $schoolId)],
            'category_id'              => ['nullable', Rule::exists('categories', 'id')->where('school_id', $schoolId)],
            'student_type_id'          => ['nullable', Rule::exists('student_types', 'id')->where('school_id', $schoolId)],
            'corresponding_relative_id'=> ['nullable', Rule::exists('corresponding_relatives', 'id')->where('school_id', $schoolId)],
            'boarding_type_id'         => ['nullable', Rule::exists('boarding_types', 'id')->where('school_id', $schoolId)],
            'father_qualification_id'  => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $schoolId)],
            'mother_qualification_id'  => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $schoolId)],

            'permanent_address'        => 'required|string',
            'permanent_country_id'     => 'required|exists:countries,id',
            'permanent_state_id'       => 'required|exists:states,id',
            'permanent_city_id'        => 'required|exists:cities,id',

            'correspondence_address'   => 'nullable|string',
            'correspondence_country_id'=> 'nullable|exists:countries,id',
            'correspondence_state_id'  => 'nullable|exists:states,id',
            'correspondence_city_id'   => 'nullable|exists:cities,id',

            'father_first_name'        => 'required|string|max:255',
            'father_last_name'         => 'required|string|max:255',
            'father_mobile_no'          => 'required|string|max:20',

            'mother_first_name'        => 'required|string|max:255',
            'mother_last_name'         => 'required|string|max:255',
            'mother_mobile_no'         => 'required|string|max:20',

            'roll_no'                  => 'required|string|max:255',
            'receipt_no'               => 'required|string|max:255',
            'admission_fee'            => 'required|numeric|min:0',

            'student_photo'            => 'nullable|image|max:2048',
            'father_photo'             => 'nullable|image|max:2048',
            'mother_photo'             => 'nullable|image|max:2048',
        ];
    }

    /**
     * WHY withValidator:
     * Rule::exists() can only check one table at a time. To verify that the submitted
     * section_id actually belongs to the submitted class_id (not just any section in
     * the school), we need a cross-field check that runs after the base rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $schoolId  = Auth::user()->school_id;
            $classId   = $this->input('class_id');
            $sectionId = $this->input('section_id');

            if ($classId && $sectionId) {
                $valid = \App\Models\Section::where('id', $sectionId)
                    ->where('class_id', $classId)
                    ->where('school_id', $schoolId)
                    ->exists();

                if (!$valid) {
                    $validator->errors()->add(
                        'section_id',
                        'The selected section does not belong to the chosen class.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'class_id.exists'         => 'The selected class is not valid for your school.',
            'section_id.exists'       => 'The selected section is not valid for your school.',
            'academic_year_id.exists' => 'The selected academic year is not valid for your school.',
        ];
    }
}
