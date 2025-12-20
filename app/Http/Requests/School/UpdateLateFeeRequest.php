<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLateFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fine_date' => 'required|integer|min:1|max:31',
            'late_fee_amount' => 'required|numeric|min:0',
        ];
    }
}
