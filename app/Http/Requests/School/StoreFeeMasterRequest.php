<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amounts' => 'required|array',
            'amounts.*' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'amounts.*.numeric' => 'The amount must be a number.',
            'amounts.*.min' => 'The amount must be at least 0.',
        ];
    }
}
