<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            && $user->isActive()
            && ($user->isSchoolAdmin() || $user->isReceptionist());
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
        ];
    }
}
