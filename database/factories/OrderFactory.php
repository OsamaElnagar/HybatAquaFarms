<?php

namespace Database\Factories;

use App\Models\Harvest;
use App\Models\HarvestOperation;
use App\Models\Order;
use App\Models\Trader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
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
            'code' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'harvest_operation_id' => HarvestOperation::factory(),
            'harvest_id' => Harvest::factory(),
            'trader_id' => Trader::factory(),
            'date' => fake()->date(),
        ];
    }
}
