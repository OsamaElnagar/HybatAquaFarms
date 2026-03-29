<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDayOff>
 */
class EmployeeDayOffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'reason' => $this->faker->randomElement(['إجازة مرضية', 'غياب', 'إجازة شخصية', null]),
        ];
    }
}
