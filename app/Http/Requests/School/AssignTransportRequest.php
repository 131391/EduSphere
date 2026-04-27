<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignTransportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = app('currentSchool')->id;

        return [
            'action' => 'required|string|in:assign,remove',
            'route_id' => [
                'required_if:action,assign',
                \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)
            ],
            'bus_stop_id' => [
                'required_if:action,assign',
                \Illuminate\Validation\Rule::exists('bus_stops', 'id')->where('school_id', $schoolId)
            ],
            'academic_year_id' => [
                'required_if:action,assign',
                \Illuminate\Validation\Rule::exists('academic_years', 'id')->where('school_id', $schoolId)
            ],
            'start_date' => 'required_if:action,assign|date',
        ];
    }
}
