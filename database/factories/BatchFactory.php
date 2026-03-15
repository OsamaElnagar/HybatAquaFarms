<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
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
            'farm_id' => Farm::factory(),
            'species_id' => null,
            'entry_date' => fake()->date(),
            'initial_quantity' => fake()->numberBetween(1000, 50000),
            'current_quantity' => fake()->numberBetween(1000, 50000),
            'initial_weight_avg' => fake()->randomFloat(3, 0.1, 5.0),
            'current_weight_avg' => fake()->randomFloat(3, 0.1, 5.0),
            'status' => 'active',
        ];
    }
}
