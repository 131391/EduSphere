<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeePaymentFactory extends Factory
{
    protected $model = FeePayment::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'fee_id' => Fee::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 10000),
            'payment_date' => $this->faker->date('Y-m-d'),
            'payment_method_id' => PaymentMethod::factory(),
            'receipt_no' => $this->faker->unique()->bothify('RCPT-###-2024-######'),
        ];
    }
}