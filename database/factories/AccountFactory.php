<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
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
            'type' => $this->faker->randomElement(AccountType::cases()),
            'is_active' => true,
            'is_treasury' => false,
        ];
    }

    public function treasury(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_treasury' => true,
            'type' => AccountType::Asset,
        ]);
    }
}
