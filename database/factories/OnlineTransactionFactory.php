<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\OnlineTransaction;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class OnlineTransactionFactory extends Factory
{
    protected $model = OnlineTransaction::class;

    public function definition(): array
    {
        return [
            'school_id'          => School::factory(),
            'student_id'         => Student::factory(),
            'fee_id'             => Fee::factory(),
            'amount'             => $this->faker->randomFloat(2, 500, 50000),
            'gateway_name'       => 'razorpay',
            'gateway_order_id'   => 'order_' . $this->faker->unique()->bothify('??????????'),
            'gateway_transaction_id' => null,
            'status'             => OnlineTransaction::STATUS_PENDING,
            'payload'            => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => OnlineTransaction::STATUS_PENDING]);
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status'                 => OnlineTransaction::STATUS_SUCCESS,
            'gateway_transaction_id' => 'pay_' . $this->faker->unique()->bothify('??????????'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status'        => OnlineTransaction::STATUS_FAILED,
            'failed_at'     => now(),
            'error_message' => 'Payment declined',
        ]);
    }
}
