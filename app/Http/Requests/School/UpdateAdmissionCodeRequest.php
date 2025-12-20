<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmissionCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('admission_code');
        
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:admission_codes,code,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Admission code is required',
            'code.unique' => 'This admission code already exists',
        ];
    }
}
