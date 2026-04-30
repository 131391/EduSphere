<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWaiverRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Per-row authorization is handled by WaiverPolicy::update via the
        // controller's $this->authorize('update', $waiver) call. Here we
        // only confirm that there is an authenticated user at all.
        return auth()->check();
    }

    public function rules(): array
    {
        $schoolId = app('currentSchool')?->id;

        return [
            'student_id'        => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'academic_year_id'  => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'fee_period'        => 'required|string|max:50',
            'actual_fee'        => 'required|numeric|min:0.01',
            'waiver_percentage' => 'required|numeric|between:0.01,100',
            'waiver_amount'     => 'required|numeric|min:0.01',
            'upto_months'       => 'nullable|integer|min:1|max:120',
            'reason'            => 'required|string|max:500',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $actual = (float) $this->input('actual_fee', 0);
            $amount = (float) $this->input('waiver_amount', 0);

            if ($amount > $actual) {
                $validator->errors()->add(
                    'waiver_amount',
                    'Waiver amount cannot exceed the actual fee of ₹' . number_format($actual, 2) . '.'
                );
            }

            // Duplicate check excluding the row being updated.
            $schoolId = app('currentSchool')?->id;
            $waiverId = $this->route('waiver')?->id;

            $exists = \App\Models\Waiver::where('school_id', $schoolId)
                ->where('student_id', $this->input('student_id'))
                ->where('academic_year_id', $this->input('academic_year_id'))
                ->where('fee_period', $this->input('fee_period'))
                ->when($waiverId, fn ($q) => $q->where('id', '!=', $waiverId))
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'fee_period',
                    'A waiver for this student already exists for the selected academic year and fee period.'
                );
            }
        });
    }
}
