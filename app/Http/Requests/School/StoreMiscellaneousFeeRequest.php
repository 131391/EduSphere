<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreMiscellaneousFeeRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:miscellaneous_fees,name,NULL,id,school_id,' . app('currentSchool')->id,
            ],
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Fee name is required',
            'name.unique' => 'This fee name already exists',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
        ];
    }
}
