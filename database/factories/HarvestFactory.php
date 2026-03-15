<?php

namespace Database\Factories;

use App\Enums\HarvestStatus;
use App\Models\Harvest;
use App\Models\HarvestOperation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Harvest>
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
            'harvest_number' => Harvest::generateHarvestNumber(),
            'harvest_operation_id' => HarvestOperation::factory(),
            'harvest_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'shift' => fake()->randomElement(['morning', 'afternoon', 'night', null]),
            'status' => HarvestStatus::Pending, // Default per migration or random
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
