<?php

namespace App\Http\Requests\School\Examination;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->currentSchoolId();

        return [
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q
                    ->where('school_id', $schoolId)
                    ->whereNull('deleted_at')),
            ],
            'exam_type_id' => [
                'required',
                Rule::exists('exam_types', 'id')->where(fn ($q) => $q
                    ->where('school_id', $schoolId)
                    ->whereNull('deleted_at')),
            ],
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.exists' => 'The selected class is invalid for this school.',
            'exam_type_id.exists' => 'The selected exam type is invalid for this school.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }

    protected function currentSchoolId(): ?int
    {
        if (!app()->bound('currentSchool')) {
            return null;
        }

        return optional(app('currentSchool'))->id;
    }
}
