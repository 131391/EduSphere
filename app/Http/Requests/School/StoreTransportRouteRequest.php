<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransportRouteRequest extends FormRequest
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
            'route_name' => 'required|string|max:255',
            'vehicle_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('vehicles', 'id')->where('school_id', $schoolId)
            ],
            'route_create_date' => 'required|date',
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\RouteStatus::class)],
        ];
    }
}
