<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition()
    {
        $name = $this->faker->randomElement(['Cash', 'Bank Transfer', 'Card', 'UPI', 'Cheque']);

        return [
            'school_id' => School::factory(),
            'name'      => $name,
            'code'      => strtoupper(str_replace(' ', '_', $name)),
            'is_active' => true,
        ];
    }
}
