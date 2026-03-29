<?php

namespace Database\Factories;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeAdvance>
 */
class EmployeeAdvanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 100, 5000);

        return [
            'employee_id' => Employee::factory(),
            'advance_number' => 'ADV-'.$this->faker->unique()->numerify('#####'),
            'amount' => $amount,
            'balance_remaining' => $amount,
            'request_date' => now()->toDateString(),
            'approval_status' => AdvanceApprovalStatus::APPROVED,
            'status' => AdvanceStatus::Active,
        ];
    }
}
