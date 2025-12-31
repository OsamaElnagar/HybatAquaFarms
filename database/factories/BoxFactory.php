<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Box>
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
            'species_id' => \App\Models\Species::factory(),
            'max_weight' => fake()->randomFloat(2, 10, 30),
            'class_total_weight' => fake()->randomFloat(2, 10, 30),
            'class' => fake()->randomElement(['1', '2', '3', 'Jumbo']),
            'category' => fake()->randomElement(['Foam', 'Plastic']),
        ];
    }
}
