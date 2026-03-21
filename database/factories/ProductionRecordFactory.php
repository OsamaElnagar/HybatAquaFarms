<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\ProductionRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionRecord>
 */
class ProductionRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_id' => BatchFactory::new(),
            'farm_id' => Farm::factory(),
            'unit_id' => FarmUnit::factory(),
            'date' => now(),
            'quantity' => $this->faker->randomFloat(2, 10, 100),
            'unit' => 'egg',
            'recorded_by' => User::factory(),
        ];
    }
}
