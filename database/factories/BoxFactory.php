<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Box>
 */
class BoxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'species_id' => Species::factory(),
            'max_weight' => fake()->randomFloat(2, 10, 30),
            'class_total_weight' => fake()->randomFloat(2, 10, 30),
            'class' => fake()->randomElement(['1', '2', '3', 'Jumbo']),
            'category' => fake()->randomElement(['Foam', 'Plastic']),
        ];
    }
}
