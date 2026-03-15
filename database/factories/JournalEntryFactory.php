<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_number' => 'JE-'.$this->faker->unique()->numerify('######'),
            'date' => now(),
            'description' => $this->faker->sentence(),
            'source_type' => 'App\Models\Account', // Dummy source for factory
            'source_id' => 1,
            'is_posted' => true,
            'posted_at' => now(),
        ];
    }
}
