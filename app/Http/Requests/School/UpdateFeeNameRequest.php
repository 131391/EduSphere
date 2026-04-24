<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            && $user->isActive()
            && ($user->isSchoolAdmin() || $user->isReceptionist());
    }

    public function rules(): array
    {
        $id = $this->route('fee_name');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:fee_names,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
            ],
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Fee name is required',
            'name.unique' => 'This fee name already exists',
        ];
    }
}
