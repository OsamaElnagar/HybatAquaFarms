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
        $boxesCount = fake()->numberBetween(5, 20);
        $totalWeight = fake()->randomFloat(3, 50, 500);
        $totalQuantity = fake()->numberBetween(100, 1000);

        return [
            'harvest_number' => 'HRV-'.date('Y').'-'.fake()->unique()->numberBetween(1000, 9999),
            'harvest_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'boxes_count' => $boxesCount,
            'total_weight' => $totalWeight,
            'average_weight_per_box' => $totalWeight / $boxesCount,
            'total_quantity' => $totalQuantity,
            'average_fish_weight' => ($totalWeight * 1000) / $totalQuantity, // convert kg to grams
            'status' => fake()->randomElement(['pending', 'completed', 'sold']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
