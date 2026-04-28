<?php

namespace App\Http\Requests\School\Examination;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignClassSubjectRequest extends FormRequest
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
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q
                    ->where('school_id', $schoolId)
                    ->whereNull('deleted_at')),
            ],
            'full_marks' => 'required|integer|min:1|max:1000',
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
