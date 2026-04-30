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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roomId = $this->input('hostel_room_id');
            $bedNo = $this->input('bed_no');
            $schoolId = app('currentSchool')->id;
            $assignment = $this->route('hostelBedAssignment')
                ?? $this->route('hostel_bed_assignment')
                ?? $this->route('assignment');

            if ($roomId && $assignment) {
                $room = \App\Models\HostelRoom::where('school_id', $schoolId)->find($roomId);
                if ($room) {
                    // Check capacity only if room is changing
                    if ($roomId != $assignment->hostel_room_id) {
                        if (!$room->hasAvailableBeds()) {
                            $validator->errors()->add('hostel_room_id', "This room has reached its maximum capacity of {$room->no_of_beds} beds.");
                        }
                    }

                    // Check bed collision if bed_no is provided
                    if ($bedNo) {
                        $collision = \App\Models\HostelBedAssignment::where('school_id', $schoolId)
                            ->where('hostel_room_id', $roomId)
                            ->where('bed_no', $bedNo)
                            ->where('id', '!=', $assignment->id)
                            ->active()
                            ->exists();
                            
                        if ($collision) {
                            $validator->errors()->add('bed_no', "Bed number '{$bedNo}' is already assigned to another student in this room.");
                        }
                    }
                }
            }
        });
    }
}
