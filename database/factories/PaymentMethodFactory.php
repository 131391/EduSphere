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
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->randomElement(['Cash', 'Bank Transfer', 'Card', 'UPI', 'Cheque']),
            'description' => $this->faker->sentence,
        ];
    }
}
