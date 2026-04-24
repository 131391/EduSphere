<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeMasterRequest extends FormRequest
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
            'class_id'    => 'required|exists:classes,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amounts'     => 'required|array',
            'amounts.*'   => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required'    => 'Please select a class.',
            'class_id.exists'      => 'The selected class is invalid.',
            'fee_type_id.required' => 'Please select an installment type.',
            'fee_type_id.exists'   => 'The selected installment type is invalid.',
            'amounts.required'     => 'Please enter at least one fee amount.',
            'amounts.array'        => 'Fee amounts must be provided as a list.',
            'amounts.*.numeric'    => 'Each fee amount must be a number.',
            'amounts.*.min'        => 'Fee amounts cannot be negative.',
        ];
    }
}
