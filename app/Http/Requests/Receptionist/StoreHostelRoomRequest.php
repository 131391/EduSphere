<?php

namespace App\Http\Requests\Receptionist;

use App\Enums\YesNo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHostelRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = app('currentSchool')->id;

        return [
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
            'room_name' => 'required|string|max:255',
            'no_of_beds' => 'required|integer|min:1',
            'ac' => ['nullable', 'integer', Rule::enum(YesNo::class)],
            'cooler' => ['nullable', 'integer', Rule::enum(YesNo::class)],
            'fan' => ['nullable', 'integer', Rule::enum(YesNo::class)],
            'room_create_date' => 'nullable|date',
        ];
    }
}
