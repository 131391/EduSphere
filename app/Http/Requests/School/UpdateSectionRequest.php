<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = Auth::user()->school_id;
        $sectionId = $this->route('id');

        return [
            'class_id'     => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'name'         => [
                'required',
                'string',
                'max:100',
                Rule::unique('sections', 'name')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('class_id', $this->input('class_id')))
                    ->ignore($sectionId)
            ],
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
            'name.unique'       => 'This section name already exists for the selected class.',
        ];
    }
}
