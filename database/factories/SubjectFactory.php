<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Mathematics', 'Science', 'English', 'Social Studies', 'Hindi',
            'Sanskrit', 'Computer Science', 'Physics', 'Chemistry', 'Biology',
        ]);

        return [
            'school_id' => School::factory(),
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 3)),
            'description' => null,
            'order' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
