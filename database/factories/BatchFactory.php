<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_code' => 'BATCH-'.fake()->unique()->numerify('#####'),
            'farm_id' => \App\Models\Farm::factory(),
            'species_id' => \App\Models\Species::factory(),
            'entry_date' => fake()->date(),
            'initial_quantity' => fake()->numberBetween(1000, 50000),
            'current_quantity' => fake()->numberBetween(1000, 50000),
            'initial_weight_avg' => fake()->randomFloat(3, 0.1, 5.0),
            'current_weight_avg' => fake()->randomFloat(3, 0.1, 5.0),
            'status' => 'active',
        ];
    }
}
