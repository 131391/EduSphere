<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
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
                'max:100',
                'unique:payment_methods,name,NULL,id,school_id,' . app('currentSchool')->id,
            ],
            'code' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Payment method name is required',
            'name.unique' => 'This payment method already exists',
        ];
    }
}
