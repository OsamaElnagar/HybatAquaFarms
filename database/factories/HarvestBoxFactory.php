<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HarvestBox>
 */
class HarvestBoxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weight = fake()->randomFloat(3, 5, 50);
        $fishCount = fake()->numberBetween(10, 100);

        return [
            'weight' => $weight,
            'fish_count' => $fishCount,
            'average_fish_weight' => ($weight * 1000) / $fishCount, // convert kg to grams
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
