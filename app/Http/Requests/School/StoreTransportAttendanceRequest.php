<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransportAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = app('currentSchool')->id;

        return [
            'vehicle_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('vehicles', 'id')->where('school_id', $schoolId)
            ],
            'route_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)
            ],
            'academic_year_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $schoolId)
            ],
            'attendance_date' => 'required|date|before_or_equal:today',
            'attendance_type' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\TransportAttendanceType::class)],
            'attendance_data' => 'required|array',
            'attendance_data.*.student_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $schoolId)
            ],
            'attendance_data.*.is_present' => 'required|boolean',
            'attendance_data.*.remarks' => 'nullable|string|max:255',
        ];
    }
}
