<?php

namespace Database\Seeders;

use App\Enums\HarvestStatus;
use App\Models\FarmUnit;
use App\Models\Harvest;
use App\Models\HarvestOperation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HarvestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operations = HarvestOperation::all();

        if ($operations->isEmpty()) {
            echo "⚠️ No harvest operations found. Run HarvestOperationSeeder first.\n";

            return;
        }

        foreach ($operations as $operation) {
            // Calculate duration
            $durationDays = Carbon::parse($operation->start_date)->diffInDays(Carbon::parse($operation->end_date)) + 1;
            $harvestDays = min($durationDays, 10); // Max 10 daily harvests per operation

            // Get units from the batch
            $units = FarmUnit::where('farm_id', $operation->farm_id)
                ->inRandomOrder()
                ->limit(rand(2, 4))
                ->get();

            if ($units->isEmpty()) {
                continue;
            }

            // Create harvests across the operation days
            for ($day = 0; $day < $harvestDays; $day++) {
                $harvestDate = Carbon::parse($operation->start_date)->addDays($day);

                $harvest = Harvest::create([
                    'harvest_operation_id' => $operation->id,
                    'harvest_number' => Harvest::generateHarvestNumber(),
                    'harvest_date' => $harvestDate,
                    'shift' => fake()->randomElement(['morning', 'afternoon', 'night', null]),
                    'status' => HarvestStatus::Completed,
                    'notes' => fake()->optional(0.2)->sentence(),
                ]);
            }
        }

        echo '✅ تم إنشاء '.Harvest::count()." حصاد بنجاح.\n";
    }
}
