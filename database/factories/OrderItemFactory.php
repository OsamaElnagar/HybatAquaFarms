<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => fn () => Order::factory(),
            'box_id' => fn () => Box::factory()->for(Species::factory()),
            'quantity' => fake()->numberBetween(1, 100),
            'weight_per_box' => fake()->randomFloat(2, 1, 50),
            'unit_price' => fake()->randomFloat(2, 10, 200),
        ];
    }
}
