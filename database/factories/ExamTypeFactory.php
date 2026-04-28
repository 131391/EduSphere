<?php

namespace Database\Factories;

use App\Models\ExamType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamTypeFactory extends Factory
{
    protected $model = ExamType::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->unique()->randomElement([
                'Unit Test', 'Mid Term', 'Half Yearly', 'Final', 'Annual', 'Pre-Board',
            ]),
        ];
    }
}
