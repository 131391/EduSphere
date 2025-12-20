<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'exists:classes,id',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'capacity' => 'required|integer|min:1|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Please select a class',
            'class_id.exists' => 'Selected class does not exist',
            'name.required' => 'Section name is required',
            'capacity.required' => 'Student max length is required',
            'capacity.min' => 'Capacity must be at least 1',
            'capacity.max' => 'Capacity cannot exceed 500',
        ];
    }
}
