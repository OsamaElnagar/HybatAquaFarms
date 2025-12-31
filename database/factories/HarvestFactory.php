<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Harvest>
 */
class HarvestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'harvest_number' => \App\Models\Harvest::generateHarvestNumber(),
            'harvest_operation_id' => \App\Models\HarvestOperation::factory(),
            'harvest_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'shift' => fake()->randomElement(['morning', 'afternoon', 'night', null]),
            'status' => \App\Enums\HarvestStatus::Pending, // Default per migration or random
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
