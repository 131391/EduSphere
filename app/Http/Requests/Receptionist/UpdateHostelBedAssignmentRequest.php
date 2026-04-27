<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHostelBedAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = app('currentSchool')->id;

        return [
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $schoolId),
            ],
            'hostel_id' => [
                'required',
                Rule::exists('hostels', 'id')->where('school_id', $schoolId),
            ],
            'hostel_floor_id' => [
                'required',
                Rule::exists('hostel_floors', 'id')
                    ->where('school_id', $schoolId)
                    ->where('hostel_id', $this->input('hostel_id')),
            ],
            'hostel_room_id' => [
                'required',
                Rule::exists('hostel_rooms', 'id')
                    ->where('school_id', $schoolId)
                    ->where('hostel_id', $this->input('hostel_id'))
                    ->where('hostel_floor_id', $this->input('hostel_floor_id')),
            ],
            'bed_no' => 'nullable|string|max:255',
            'rent' => 'nullable|numeric|min:0',
            'hostel_assign_date' => 'nullable|date',
            'starting_month' => 'nullable|string|max:255',
        ];
    }
}
