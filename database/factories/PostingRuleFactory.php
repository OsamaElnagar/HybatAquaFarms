<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostingRule>
 */
class PostingRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_key' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'debit_account_id' => Account::factory(),
            'credit_account_id' => Account::factory(),
            'is_active' => true,
        ];
    }
}
