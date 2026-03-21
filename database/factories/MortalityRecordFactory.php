<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\MortalityRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MortalityRecord>
 */
class MortalityRecordFactory extends Factory
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
            'quantity' => $this->faker->numberBetween(1, 5),
            'reason' => 'Heat',
            'recorded_by' => User::factory(),
        ];
    }
}
