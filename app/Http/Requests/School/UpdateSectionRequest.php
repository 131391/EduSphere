<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id'     => ['required', 'exists:classes,id'],
            'name'         => ['required', 'string', 'max:100'],
            'capacity'     => 'nullable|integer|min:1|max:500',
            'is_available' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Please select a class.',
            'class_id.exists'   => 'The selected class does not exist.',
            'name.required'     => 'Section name is required.',
        ];
    }
}
