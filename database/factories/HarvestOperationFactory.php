<?php

namespace Database\Factories;

use App\Enums\HarvestOperationStatus;
use App\Models\Farm;
use App\Models\HarvestOperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HarvestOperation>
 */
class HarvestOperationFactory extends Factory
{
    protected $model = HarvestOperation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operation_number' => 'OP-'.fake()->unique()->randomNumber(5, true),
            'batch_id' => BatchFactory::new(),
            'farm_id' => Farm::factory(),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => HarvestOperationStatus::Ongoing,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
