<?php

namespace Database\Factories;

use App\Models\School;
use App\Enums\SchoolStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'code' => $this->faker->unique()->bothify('SCH###'),
            'email' => $this->faker->unique()->companyEmail,
            'status' => SchoolStatus::Active,
        ];
    }
}
