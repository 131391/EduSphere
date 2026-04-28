<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    protected $model = Result::class;

    public function definition(): array
    {
        $total = $this->faker->randomElement([50, 75, 100]);
        $obtained = $this->faker->numberBetween(0, (int) $total);
        $percentage = $total > 0 ? round(($obtained / $total) * 100, 2) : 0.0;

        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'exam_id' => Exam::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => ClassModel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'marks_obtained' => $obtained,
            'total_marks' => $total,
            'percentage' => $percentage,
            'grade' => null,
            'remarks' => null,
            'is_absent' => false,
            'entered_by' => null,
            'locked_at' => null,
        ];
    }

    public function absent(): self
    {
        return $this->state(fn () => [
            'is_absent' => true,
            'marks_obtained' => 0,
            'percentage' => 0,
            'grade' => null,
        ]);
    }

    public function locked(): self
    {
        return $this->state(fn () => ['locked_at' => now()]);
    }
}
