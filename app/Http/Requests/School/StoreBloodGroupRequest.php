<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreBloodGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:10',
                'unique:blood_groups,name,NULL,id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Blood group name is required',
            'name.unique' => 'This blood group already exists',
        ];
    }
}
