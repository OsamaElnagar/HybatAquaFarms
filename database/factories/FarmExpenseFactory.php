<?php

namespace Database\Factories;

use App\Enums\FarmExpenseType;
use App\Models\Account;
use App\Models\Farm;
use App\Models\FarmExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmExpense>
 */
class FarmExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'type' => fake()->randomElement(FarmExpenseType::cases()),
            'treasury_account_id' => Account::factory(),
            'account_id' => Account::factory(),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'date' => fake()->date(),
            'description' => fake()->sentence(),
        ];
    }
}
