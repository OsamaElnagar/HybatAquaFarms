<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(\App\Enums\AccountType::cases()),
            'is_active' => true,
            'is_treasury' => false,
        ];
    }

    public function treasury(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_treasury' => true,
            'type' => \App\Enums\AccountType::Asset,
        ]);
    }
}
