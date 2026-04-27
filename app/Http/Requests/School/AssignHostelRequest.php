<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignHostelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = app('currentSchool')->id;

        return [
            'action'             => 'required|in:assign,remove',
            'hostel_id'          => [
                'required_if:action,assign',
                Rule::exists('hostels', 'id')->where('school_id', $schoolId),
            ],
            'hostel_floor_id'    => [
                'required_if:action,assign',
                Rule::exists('hostel_floors', 'id')
                    ->where('school_id', $schoolId)
                    ->where('hostel_id', $this->input('hostel_id')),
            ],
            'hostel_room_id'     => [
                'required_if:action,assign',
                Rule::exists('hostel_rooms', 'id')
                    ->where('school_id', $schoolId)
                    ->where('hostel_id', $this->input('hostel_id'))
                    ->where('hostel_floor_id', $this->input('hostel_floor_id')),
            ],
            'bed_no'             => 'nullable|string|max:255',
            'rent'               => 'nullable|numeric|min:0',
            'hostel_assign_date' => 'nullable|date',
            'start_date'         => 'required_if:action,assign|date',
        ];
    }
}
