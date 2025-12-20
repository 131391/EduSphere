<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('student_type');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:student_types,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Student type name is required',
            'name.unique' => 'This student type already exists',
        ];
    }
}
