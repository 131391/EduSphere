<?php

namespace App\Http\Requests\School\Examination;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->currentSchoolId();

        return [
            'exam_id' => [
                'required',
                Rule::exists('exams', 'id')->where(fn ($q) => $q
                    ->where('school_id', $schoolId)
                    ->whereNull('deleted_at')),
            ],
            'exam_subject_id' => [
                'required',
                Rule::exists('exam_subjects', 'id')->where(fn ($q) => $q
                    ->where('exam_id', $this->input('exam_id'))),
            ],
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($q) => $q
                    ->where('school_id', $schoolId)
                    ->whereNull('deleted_at')),
            ],
            'marks.*.marks_obtained' => 'nullable|numeric|min:0|max:999999.99',
            'marks.*.is_absent' => 'nullable|boolean',
            'marks.*.remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_id.exists' => 'The selected exam is invalid for this school.',
            'exam_subject_id.exists' => 'The selected subject does not belong to this exam.',
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
