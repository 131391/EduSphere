<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:classes,name,NULL,id,school_id,' . app('currentSchool')->id,
            ],
            'order' => 'nullable|integer|min:1|max:100',
            'is_available' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Class name is required',
            'name.unique' => 'This class name already exists in your school',
            'name.max' => 'Class name cannot exceed 100 characters',
            'order.integer' => 'Order must be a number',
            'order.min' => 'Order must be at least 1',
            'order.max' => 'Order cannot exceed 100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'class name',
            'order' => 'display order',
            'is_available' => 'availability status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available' => $this->has('is_available') ? (bool) $this->is_available : true,
        ]);
    }
}
