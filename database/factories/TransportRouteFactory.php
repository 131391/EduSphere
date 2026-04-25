<?php

namespace Database\Factories;

use App\Models\TransportRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransportRoute>
 */
class TransportRouteFactory extends Factory
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
            'route_name' => $this->faker->unique()->city . ' Route',
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'route_create_date' => now()->format('Y-m-d'),
            'status' => \App\Enums\RouteStatus::Active,
        ];
    }
}
