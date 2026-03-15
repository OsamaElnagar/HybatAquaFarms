<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_number' => $this->faker->unique()->numerify('EMP-####'),
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'hire_date' => $this->faker->date(),
            'basic_salary' => $this->faker->randomFloat(2, 3000, 10000),
            'status' => 'active',
        ];
    }
}
