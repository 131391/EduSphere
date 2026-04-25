<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => \App\Models\School::factory(),
            'registration_no' => $this->faker->unique()->bothify('MH##??####'),
            'vehicle_no' => $this->faker->unique()->bothify('VEH-###'),
            'fuel_type' => $this->faker->randomElement([1, 2, 3, 4]),
            'capacity' => $this->faker->numberBetween(10, 50),
            'initial_reading' => $this->faker->numberBetween(0, 10000),
            'engine_no' => $this->faker->unique()->bothify('ENG#######'),
            'chassis_no' => $this->faker->unique()->bothify('CHS#######'),
            'vehicle_type' => 'Bus',
            'model_no' => 'Tata Marcopolo',
            'date_of_purchase' => $this->faker->date(),
            'vehicle_group' => 'Standard',
            'manufacturing_year' => $this->faker->year(),
            'vehicle_create_date' => now()->format('Y-m-d'),
        ];
    }
}
