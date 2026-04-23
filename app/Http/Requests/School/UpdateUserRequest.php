<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return auth()->check()
            && $user
            && auth()->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:teacher,receptionist,accountant,librarian',
            'phone' => 'nullable|string|max:20',
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'suspended'])],
        ];
    }
}
