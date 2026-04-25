<?php

namespace App\Http\Requests\School;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransportAttendanceRequest extends FormRequest
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id' => 'required|exists:transport_routes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'attendance_type' => 'required|integer|in:1,2', // Pickup, Drop
            'attendance_data' => 'required|array',
            'attendance_data.*.student_id' => 'required|exists:students,id',
            'attendance_data.*.is_present' => 'required|boolean',
            'attendance_data.*.remarks' => 'nullable|string|max:255',
        ];
    }
}
