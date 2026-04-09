<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaiverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isSchoolAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_period' => 'required|string|max:255',
            'actual_fee' => 'required|numeric|min:0',
            'waiver_percentage' => 'nullable|numeric|between:0,100',
            'waiver_amount' => 'nullable|numeric|min:0',
            'upto_months' => 'nullable|integer|min:1',
            'reason' => 'required|string|max:500',
        ];
    }
}
