<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\PartnerLoan;
use App\Models\Trader;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerLoan>
 */
class PartnerLoanFactory extends Factory
{
    protected $model = PartnerLoan::class;

    public function definition(): array
    {
        return [
            'loanable_type' => Trader::class,
            'loanable_id' => Trader::factory(),
            'date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'treasury_account_id' => fn () => Account::where('is_treasury', true)->first()?->id ?? Account::factory(),
            'description' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function forTrader(Trader $trader): static
    {
        return $this->state(fn () => [
            'loanable_type' => Trader::class,
            'loanable_id' => $trader->id,
        ]);
    }

    public function forFactory(\App\Models\Factory $factory): static
    {
        return $this->state(fn () => [
            'loanable_type' => \App\Models\Factory::class,
            'loanable_id' => $factory->id,
        ]);
    }
}
