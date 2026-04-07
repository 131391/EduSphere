<?php

namespace App\Livewire\Traits;

trait WithValidation
{
    protected array $validationRules = [];
    protected array $validationMessages = [];
    protected array $validationAttributes = [];

    public function rules(): array
    {
        return $this->validationRules;
    }

    public function messages(): array
    {
        return $this->validationMessages;
    }

    public function attributes(): array
    {
        return $this->validationAttributes;
    }

    public function validateForm(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyError('Please fix the validation errors');
            return false;
        }
    }

    public function validateField(string $field): bool
    {
        try {
            $this->validateOnly($field);
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return false;
        }
    }
}
