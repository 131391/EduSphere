<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBusStopRequest extends FormRequest
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
            'route_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)
            ],
            'vehicle_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('vehicles', 'id')->where('school_id', $schoolId)
            ],
            'bus_stop_no' => 'required|string|max:50',
            'bus_stop_name' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'distance_from_institute' => 'nullable|numeric|min:0',
            'charge_per_month' => 'required|numeric|min:0',
            'area_pin_code' => 'nullable|string|max:20',
        ];
    }
}
