<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHostelFloorRequest extends FormRequest
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
            'floor_name' => 'required|string|max:255',
            'total_room' => 'nullable|integer|min:0',
            'floor_create_date' => 'nullable|date',
        ];
    }
}
