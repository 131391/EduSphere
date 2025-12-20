<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('boarding_type');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:boarding_types,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Boarding type name is required',
            'name.unique' => 'This boarding type already exists',
        ];
    }
}
