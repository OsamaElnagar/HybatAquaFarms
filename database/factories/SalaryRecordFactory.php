<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryRecord>
 */
class SalaryRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'pay_period_start' => now()->startOfMonth(),
            'pay_period_end' => now()->endOfMonth(),
            'basic_salary' => $this->faker->randomFloat(2, 3000, 10000),
            'bonuses' => 0,
            'deductions' => 0,
            'advances_deducted' => 0,
            'net_salary' => $this->faker->randomFloat(2, 3000, 10000),
            'payment_date' => now(),
            'status' => \App\Enums\SalaryStatus::PENDING,
        ];
    }
}
