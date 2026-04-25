<?php

namespace Database\Factories;

use App\Models\BusStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusStop>
 */
class BusStopFactory extends Factory
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
            'route_id' => \App\Models\TransportRoute::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'bus_stop_no' => 'S' . $this->faker->numberBetween(1, 100),
            'bus_stop_name' => $this->faker->streetName,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'distance_from_institute' => $this->faker->randomFloat(2, 1, 50),
            'charge_per_month' => $this->faker->randomFloat(2, 500, 5000),
            'area_pin_code' => $this->faker->postcode,
        ];
    }
}
