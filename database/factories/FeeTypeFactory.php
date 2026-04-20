<?php

namespace Database\Factories;

use App\Models\FeeType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeTypeFactory extends Factory
{
    protected $model = FeeType::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->randomElement(['Tuition Fee', 'Transport Fee', 'Hostel Fee', 'Library Fee', 'Exam Fee']),
            'description' => $this->faker->sentence,
        ];
    }
}
