<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
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
            'national_id' => $this->faker->numerify('##############'),
            'hire_date' => $this->faker->date(),
            'basic_salary' => $this->faker->randomFloat(2, 3000, 10000),
            'status' => 'active',
        ];
    }
}
