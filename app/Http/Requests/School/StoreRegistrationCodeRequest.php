<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:registration_codes,code,NULL,id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Registration code is required',
            'code.unique' => 'This registration code already exists',
        ];
    }
}
