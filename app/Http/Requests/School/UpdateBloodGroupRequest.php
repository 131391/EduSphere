<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBloodGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('blood_group');
        
        return [
            'name' => [
                'required',
                'string',
                'max:10',
                'unique:blood_groups,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
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
