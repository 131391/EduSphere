<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $start = Carbon::parse($this->faker->dateTimeBetween('-1 month', '+2 months'));
        $end = $start->copy()->addDays($this->faker->numberBetween(1, 7));

        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'class_id' => ClassModel::factory(),
            'exam_type_id' => ExamType::factory(),
            'name' => null,
            'code' => null,
            'month' => $start->format('F Y'),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'description' => null,
            'status' => ExamStatus::Scheduled,
        ];
    }

    public function ongoing(): self
    {
        return $this->state(fn () => [
            'status' => ExamStatus::Ongoing,
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => ExamStatus::Completed,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => ['status' => ExamStatus::Cancelled]);
    }

    public function locked(): self
    {
        return $this->state(fn () => ['status' => ExamStatus::Locked]);
    }
}
