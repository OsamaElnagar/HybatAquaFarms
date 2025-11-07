<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Farm>
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
