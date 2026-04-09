<?php

namespace App\Http\Requests\Admin;

use App\Enums\SchoolStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $schoolId = $this->route('school');

        return [
            // School Details
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('schools', 'code')->ignore($schoolId)],
            'subdomain' => ['required', 'string', 'max:255', Rule::unique('schools', 'subdomain')->ignore($schoolId)],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('schools', 'domain')->ignore($schoolId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('schools', 'email')->ignore($schoolId)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'pincode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => ['required', 'integer', Rule::enum(SchoolStatus::class)],
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after:subscription_start_date',

            // Admin Details
            'admin_id' => 'nullable|exists:users,id',
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => [
                'nullable', 
                'email', 
                'max:255', 
                Rule::unique('users', 'email')->ignore($this->input('admin_id'))
            ],
            'admin_password' => 'nullable|string|min:8|confirmed',
        ];
    }
}
