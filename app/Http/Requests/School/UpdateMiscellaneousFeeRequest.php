<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMiscellaneousFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('miscellaneous_fee');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:miscellaneous_fees,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
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
