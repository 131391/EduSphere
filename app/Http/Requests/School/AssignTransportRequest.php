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
        return [
            'action' => 'required|string|in:assign,remove',
            'route_id' => 'required_if:action,assign|exists:transport_routes,id',
            'bus_stop_id' => 'required_if:action,assign|exists:bus_stops,id',
            'academic_year_id' => 'required_if:action,assign|exists:academic_years,id',
            'start_date' => 'required_if:action,assign|date',
        ];
    }
}
