<?php

namespace App\Http\Requests\School\Examination;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ];
    }
}
