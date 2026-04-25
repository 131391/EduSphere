<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Enums\StudentStatus;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'admission_no' => $this->faker->unique()->numberBetween(100000, 999999),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'dob' => $this->faker->date('Y-m-d', '2015-12-31'),
            'gender' => Gender::Male,
            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'class_id' => ClassModel::factory(),
            'section_id' => Section::factory(),
            'status' => StudentStatus::Active,
            'admission_date' => $this->faker->date('Y-m-d', '2024-04-01'),
            'mobile_no' => $this->faker->unique()->phoneNumber,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudentStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StudentStatus::Inactive,
        ]);
    }
}
