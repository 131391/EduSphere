<?php

namespace App\Http\Requests\School\Examination;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exam_types', 'name')
                    ->where(fn ($q) => $q
                        ->where('school_id', $this->currentSchoolId())
                        ->whereNull('deleted_at')),
            ],
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
