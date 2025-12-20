<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdmissionFeeRequest extends FormRequest
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
                Rule::unique('admission_fees')->where(function ($query) {
                    return $query->where('school_id', auth()->user()->school_id);
                }),
            ],
            'amount' => 'required|numeric|min:0',
        ];
    }
}
