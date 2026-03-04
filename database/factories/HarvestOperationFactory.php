<?php

namespace Database\Factories;

use App\Enums\HarvestOperationStatus;
use App\Models\HarvestOperation;
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
            'operation_number' => HarvestOperation::generateOperationNumber(),
            'batch_id' => \App\Models\Batch::factory(),
            'farm_id' => \App\Models\Farm::factory(),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => HarvestOperationStatus::Ongoing,
            'notes' => fake()->optional()->sentence(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
