<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQualificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('qualification');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:qualifications,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Qualification name is required',
            'name.unique' => 'This qualification already exists',
        ];
    }
}
