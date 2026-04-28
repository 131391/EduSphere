<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        $start = $this->faker->numberBetween(0, 80);
        $end = min(100, $start + $this->faker->numberBetween(5, 20));

        return [
            'school_id' => School::factory(),
            'range_start' => $start,
            'range_end' => $end,
            'grade' => $this->faker->randomElement(['A+', 'A', 'B', 'C', 'D', 'F']),
        ];
    }

    /**
     * Seed a complete five-band 0–100 set on the given school. The result is a
     * Collection of Grade rows with bands [0–32, 33–49, 50–69, 70–84, 85–100].
     */
    public function fullCoverage(): self
    {
        return $this->state(fn () => []);
    }

    /**
     * Helper to install a canonical 0–100 band set on a school.
     *
     * @return \Illuminate\Support\Collection<int, Grade>
     */
    public static function seedCanonicalBands(School $school): \Illuminate\Support\Collection
    {
        $bands = collect([
            ['range_start' => 0,  'range_end' => 32,  'grade' => 'F'],
            ['range_start' => 33, 'range_end' => 49,  'grade' => 'D'],
            ['range_start' => 50, 'range_end' => 69,  'grade' => 'C'],
            ['range_start' => 70, 'range_end' => 84,  'grade' => 'B'],
            ['range_start' => 85, 'range_end' => 100, 'grade' => 'A'],
        ]);

        return $bands->map(fn ($band) => Grade::create(array_merge($band, [
            'school_id' => $school->id,
        ])));
    }
}
