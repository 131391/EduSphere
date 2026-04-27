<?php

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHostelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hostel_name' => 'required|string|max:255',
            'hostel_incharge' => 'nullable|string|max:255',
            'capability' => 'nullable|integer|min:1',
            'hostel_create_date' => 'nullable|date',
        ];
    }
}
