<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCorrespondingRelativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('corresponding_relative');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:corresponding_relatives,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Relative name is required',
            'name.unique' => 'This relative already exists',
        ];
    }
}
