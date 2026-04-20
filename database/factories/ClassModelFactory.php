<?php

namespace Database\Factories;

use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->randomElement(['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5']),
            'numeric_name' => $this->faker->numberBetween(1, 12),
            'description' => $this->faker->sentence,
        ];
    }
}
