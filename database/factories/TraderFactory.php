<?php

namespace Database\Factories;

use App\Models\Trader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trader>
 */
class TraderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('TRD-###'),
            'name' => fake()->company(),
            'is_active' => true,
        ];
    }
}
