<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReligionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('religion');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:religions,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Religion name is required',
            'name.unique' => 'This religion already exists',
        ];
    }
}
