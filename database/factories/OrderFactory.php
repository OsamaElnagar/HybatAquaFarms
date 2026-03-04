<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'ORD-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'harvest_operation_id' => \App\Models\HarvestOperation::factory(),
            'harvest_id' => \App\Models\Harvest::factory(),
            'trader_id' => \App\Models\Trader::factory(),
            'date' => fake()->date(),
        ];
    }
}
