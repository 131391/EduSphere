<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeeType;
use App\Models\School;
use App\Models\Student;
use App\Enums\FeeStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'fee_type_id' => FeeType::factory(),
            'fee_name_id' => FeeName::factory(),
            'class_id' => ClassModel::factory(),
            'bill_no' => $this->faker->unique()->bothify('BILL-####'),
            'fee_period' => 'Annual',
            'payable_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'paid_amount' => 0,
            'due_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'waiver_amount' => 0,
            'discount_amount' => 0,
            'late_fee' => 0,
            'due_date' => $this->faker->date('Y-m-d', '2024-12-31'),
            'payment_status' => FeeStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => FeeStatus::Paid,
            'paid_amount' => $attributes['payable_amount'] ?? 10000,
            'due_amount' => 0,
            'payment_date' => now()->toDateString(),
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => FeeStatus::Partial,
            'paid_amount' => 5000,
            'due_amount' => 5000,
            'payment_date' => now()->toDateString(),
        ]);
    }
}