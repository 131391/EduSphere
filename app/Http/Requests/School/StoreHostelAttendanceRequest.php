<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostelAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hostel_id' => 'required|exists:hostels,id',
            'hostel_floor_id' => 'required|exists:hostel_floors,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'attendance_date' => 'required|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'attendance_data' => 'required|array|min:1',
            'attendance_data.*.student_id' => 'required|exists:students,id',
            'attendance_data.*.is_present' => 'required|boolean',
            'attendance_data.*.remarks' => 'nullable|string|max:255',
        ];
    }
}
