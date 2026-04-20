<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmissionFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'exists:classes,id',
                \Illuminate\Validation\Rule::unique('admission_fees')->where(function ($query) {
                    return $query->where('school_id', auth()->user()->school_id);
                })->ignore($this->route('admission_fee')),
            ],
            'amount' => 'required|numeric|min:0',
        ];
    }
}
