<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('payment_method');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:payment_methods,name,' . $id . ',id,school_id,' . app('currentSchool')->id,
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
