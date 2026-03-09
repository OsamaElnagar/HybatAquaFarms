<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\RepaymentType;
use App\Models\Account;
use App\Models\PartnerLoan;
use App\Models\PartnerLoanRepayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerLoanRepayment>
 */
class PartnerLoanRepaymentFactory extends Factory
{
    protected $model = PartnerLoanRepayment::class;

    public function definition(): array
    {
        return [
            'partner_loan_id' => PartnerLoan::factory(),
            'repayment_type' => RepaymentType::Cash,
            'date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 500, 10000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'treasury_account_id' => fn () => Account::where('is_treasury', true)->first()?->id ?? Account::factory(),
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function netting(): static
    {
        return $this->state(fn () => [
            'repayment_type' => RepaymentType::Netting,
            'payment_method' => null,
            'treasury_account_id' => null,
        ]);
    }
}
