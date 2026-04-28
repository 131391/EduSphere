<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamSubjectFactory extends Factory
{
    protected $model = ExamSubject::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => null,
            'subject_name' => $this->faker->randomElement(['Maths', 'Science', 'English']),
            'full_marks' => $this->faker->randomElement([50, 75, 100]),
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
