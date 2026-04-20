<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\StudentRegistration;
use App\Enums\AdmissionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentRegistrationFactory extends Factory
{
    protected $model = StudentRegistration::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'registration_no' => $this->faker->unique()->bothify('REG-######'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'dob' => $this->faker->date('Y-m-d', '2015-12-31'),
            'gender' => 1,
            'father_name' => $this->faker->name('male'),
            'father_mobile_no' => $this->faker->phoneNumber,
            'registration_date' => $this->faker->date('Y-m-d'),
            'admission_status' => AdmissionStatus::Registered,
        ];
    }

    public function admitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'admission_status' => AdmissionStatus::Admitted,
        ]);
    }
}
