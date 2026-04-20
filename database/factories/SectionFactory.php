<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Section;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'class_id' => ClassModel::factory(),
            'name' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
