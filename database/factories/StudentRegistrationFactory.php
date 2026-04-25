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
            'academic_year_id' => \App\Models\AcademicYear::factory(),
            'class_id' => \App\Models\ClassModel::factory(),
            'registration_no' => $this->faker->unique()->bothify('REG-######'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'dob' => $this->faker->date('Y-m-d', '2015-12-31'),
            'gender' => 1,
            'mobile_no' => $this->faker->phoneNumber,
            'father_first_name' => $this->faker->firstName('male'),
            'father_last_name' => $this->faker->lastName,
            'father_mobile_no' => $this->faker->phoneNumber,
            'mother_first_name' => $this->faker->firstName('female'),
            'mother_last_name' => $this->faker->lastName,
            'mother_mobile_no' => $this->faker->phoneNumber,
            'permanent_address' => $this->faker->address,
            'permanent_pin' => $this->faker->postcode,
            'registration_date' => $this->faker->date('Y-m-d'),
            'admission_status' => AdmissionStatus::Pending,
        ];
    }

    public function admitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'admission_status' => AdmissionStatus::Admitted,
        ]);
    }
}
