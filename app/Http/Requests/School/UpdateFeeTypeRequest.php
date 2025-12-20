<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $feeTypeId = $this->route('fee_type');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:fee_types,name,' . $feeTypeId . ',id,school_id,' . app('currentSchool')->id,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Fee type name is required',
            'name.unique' => 'This fee type already exists',
        ];
    }
}
