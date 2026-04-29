<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHostelAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = auth()->user()->school_id;

        return [
            'hostel_id' => [
                'required',
                Rule::exists('hostels', 'id')->where('school_id', $schoolId),
            ],
            'attendance_date' => 'required|date|before_or_equal:today',
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],
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
