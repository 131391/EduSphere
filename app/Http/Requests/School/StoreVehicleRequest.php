<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
        return [
            'registration_no' => 'required|string|max:255',
            'fuel_type' => 'required|integer|in:1,2,3,4',
            'capacity' => 'required|integer|min:1',
            'initial_reading' => 'nullable|integer|min:0',
            'engine_no' => 'nullable|string|max:255',
            'chassis_no' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'model_no' => 'nullable|string|max:255',
            'date_of_purchase' => 'nullable|date',
            'vehicle_group' => 'nullable|string|max:255',
            'imei_gps_device' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:255',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ];
    }
}
