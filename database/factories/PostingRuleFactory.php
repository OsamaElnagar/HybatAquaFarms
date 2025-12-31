<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostingRule>
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
            'debit_account_id' => \App\Models\Account::factory(),
            'credit_account_id' => \App\Models\Account::factory(),
            'is_active' => true,
        ];
    }
}
