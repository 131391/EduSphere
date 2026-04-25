<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->unique()->year() . ' - ' . ($this->faker->year() + 1),
            'start_date' => $this->faker->date('Y-m-d', '2024-04-01'),
            'end_date' => $this->faker->date('Y-m-d', '2025-03-31'),
            'is_current' => true,
        ];
    }
}
