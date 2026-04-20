<?php

namespace Database\Factories;

use App\Models\FeeName;
use App\Models\FeeType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeNameFactory extends Factory
{
    protected $model = FeeName::class;

    public function definition()
    {
        return [
            'school_id' => School::factory(),
            'fee_type_id' => FeeType::factory(),
            'name' => $this->faker->randomElement(['Annual Fee', 'Monthly Fee', 'Quarterly Fee', 'Admission Fee']),
            'description' => $this->faker->sentence,
        ];
    }
}
