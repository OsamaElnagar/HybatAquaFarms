<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\FarmUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmUnit>
 */
class FarmUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'farm_id' => fn () => Farm::factory(),
            'code' => 'UNIT-'.fake()->unique()->numberBetween(100, 999),
            'unit_type' => fake()->randomElement(['pond', 'tank', 'cage']),
        ];
    }
}
