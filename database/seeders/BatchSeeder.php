<?php

namespace Database\Seeders;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\Species;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $species = Species::where('type', 'fish')->get();

        Farm::each(function ($farm) use ($species) {
            $units = FarmUnit::where('farm_id', $farm->id)
                ->where('status', 'active')
                ->get();

            foreach ($units->take(rand(2, 4)) as $unit) {
                Batch::create([
                    'batch_code' => "{$farm->code}-B-".now()->format('Ymd').'-'.rand(1000, 9999),
                    'farm_id' => $farm->id,
                    'unit_id' => $unit->id,
                    'species_id' => $species->random()->id,
                    'entry_date' => now()->subDays(rand(30, 180)),
                    'initial_quantity' => rand(5000, 20000),
                    'current_quantity' => rand(4000, 18000),
                    'initial_weight_avg' => rand(5, 20),
                    'current_weight_avg' => rand(50, 250),
                    'source' => ['hatchery', 'purchase', 'transfer'][rand(0, 2)],
                    'status' => BatchStatus::Active,
                ]);
            }
        });
    }
}
