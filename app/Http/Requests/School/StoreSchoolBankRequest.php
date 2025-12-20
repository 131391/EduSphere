<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:100',
            'account_number' => [
                'required',
                'string',
                'max:50',
                'unique:school_banks,account_number,NULL,id,school_id,' . app('currentSchool')->id,
            ],
            'branch_name' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required' => 'Bank name is required',
            'account_number.required' => 'Account number is required',
            'account_number.unique' => 'This account number already exists',
        ];
    }
}
