<?php

namespace Database\Seeders;

use App\Enums\BatchSource;
use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Factory;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\Species;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $species = Species::where('type', 'fish')->get();

        // Get hatcheries (batch suppliers)
        $hatcheries = Factory::whereIn('code', [
            'HAT-ABD', 'HAT-NAD', 'HAT-ALI', 'HAT-MOH', 'HAT-ELK',
        ])->get();

        // Fallback to any factory if hatcheries don't exist yet
        if ($hatcheries->isEmpty()) {
            $hatcheries = Factory::all();
        }

        Farm::each(function ($farm) use ($species, $hatcheries) {
            $units = FarmUnit::where('farm_id', $farm->id)
                ->where('status', 'active')
                ->get();

            foreach ($units->take(rand(2, 4)) as $unit) {
                // 70% of batches come from hatcheries (have factory_id and cost)
                $hasFactory = rand(1, 10) <= 7 && ! $hatcheries->isEmpty();
                $factory = $hasFactory ? $hatcheries->random() : null;

                $initialQuantity = rand(5000, 20000);
                $unitCost = $hasFactory ? rand(2, 8) : null; // Cost per unit in EGP
                $totalCost = $hasFactory && $unitCost ? $initialQuantity * $unitCost : null;

                $batch = Batch::create([
                    'batch_code' => "{$farm->code}-B-".now()->format('Ymd').'-'.rand(1000, 9999),
                    'farm_id' => $farm->id,
                    'species_id' => $species->random()->id,
                    'factory_id' => $factory?->id,
                    'entry_date' => now()->subDays(rand(30, 180)),
                    'initial_quantity' => $initialQuantity,
                    'current_quantity' => rand(4000, (int) ($initialQuantity * 0.9)),
                    'initial_weight_avg' => rand(5, 20),
                    'current_weight_avg' => rand(50, 250),
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'source' => $hasFactory ? BatchSource::Hatchery : [BatchSource::Purchase, BatchSource::Transfer][rand(0, 1)],
                    'status' => BatchStatus::Active,
                ]);

                $batch->units()->attach($unit->id);
            }
        });
    }
}
