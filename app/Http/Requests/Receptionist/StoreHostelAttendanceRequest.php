<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHostelAttendanceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = auth()->user()->school_id;

        return [
            'hostel_id' => [
                'required',
                Rule::exists('hostels', 'id')->where('school_id', $schoolId),
            ],
            'hostel_floor_id' => [
                'required',
                Rule::exists('hostel_floors', 'id')->where('school_id', $schoolId)->where('hostel_id', $this->hostel_id),
            ],
            'hostel_room_id' => [
                'required',
                Rule::exists('hostel_rooms', 'id')->where('school_id', $schoolId)->where('hostel_floor_id', $this->hostel_floor_id),
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
            'attendance_date' => 'required|date|before_or_equal:today',
            'attendance_data' => 'required|array|min:1',
            'attendance_data.*.student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $schoolId),
            ],
            'attendance_data.*.is_present' => 'required|boolean',
            'attendance_data.*.remarks' => 'nullable|string|max:500',
        ];
    }
}
