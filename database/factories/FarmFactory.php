<?php

namespace Database\Factories;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('FARM-###'),
            'name' => fake()->name(),
            'size' => fake()->numberBetween(10, 200),
            'location' => fake()->city(),
            'status' => 'active',
        ];
    }
}
