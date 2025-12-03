<?php

namespace Database\Seeders;

use App\Enums\HarvestStatus;
use App\Enums\PricingUnit;
use App\Models\FarmUnit;
use App\Models\Harvest;
use App\Models\HarvestBox;
use App\Models\HarvestOperation;
use App\Models\HarvestUnit;
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
                    'batch_id' => $operation->batch_id,
                    'farm_id' => $operation->farm_id,
                    'harvest_date' => $harvestDate,
                    'shift' => fake()->randomElement(['morning', 'afternoon', 'night', null]),
                    'status' => HarvestStatus::Completed,
                    'recorded_by' => 1,
                    'notes' => fake()->optional(0.2)->sentence(),
                ]);

                // Link harvested units
                $harvestedUnits = $units->random(rand(1, min(2, $units->count())));

                foreach ($harvestedUnits as $unit) {
                    $fishBefore = rand(500, 2000);
                    $fishHarvested = rand(200, (int) ($fishBefore * 0.8));

                    HarvestUnit::create([
                        'harvest_id' => $harvest->id,
                        'unit_id' => $unit->id,
                        'fish_count_before' => $fishBefore,
                        'fish_count_harvested' => $fishHarvested,
                        'notes' => fake()->optional(0.1)->sentence(),
                    ]);
                }

                // Create boxes for this harvest with classifications
                $boxCount = rand(5, 15);
                $classifications = [
                    'بلطي' => ['min_weight' => 30, 'max_weight' => 60],
                    'نمرة 1' => ['min_weight' => 35, 'max_weight' => 55],
                    'نمرة 2' => ['min_weight' => 25, 'max_weight' => 45],
                    'نمرة 3' => ['min_weight' => 20, 'max_weight' => 35],
                    'نمرة 4' => ['min_weight' => 15, 'max_weight' => 25],
                    'جامبو' => ['min_weight' => 50, 'max_weight' => 80],
                    'خرط' => ['min_weight' => 10, 'max_weight' => 20],
                ];

                for ($boxNum = 1; $boxNum <= $boxCount; $boxNum++) {
                    $classification = fake()->randomElement(array_keys($classifications));
                    $classData = $classifications[$classification];

                    $weight = fake()->randomFloat(3, $classData['min_weight'], $classData['max_weight']);
                    $fishCount = rand(50, 300);

                    HarvestBox::create([
                        'harvest_id' => $harvest->id,
                        'harvest_operation_id' => $operation->id,
                        'batch_id' => $operation->batch_id,
                        'species_id' => $operation->batch->species_id,
                        'box_number' => $boxNum,
                        'classification' => $classification,
                        'grade' => fake()->randomElement(['A', 'B', 'C', null]),
                        'size_category' => fake()->randomElement(['small', 'medium', 'large', 'jumbo', null]),
                        'weight' => $weight,
                        'fish_count' => $fishCount,
                        'pricing_unit' => PricingUnit::Kilogram->value,
                        // Initially not sold
                        'is_sold' => false,
                        'notes' => fake()->optional(0.1)->sentence(),
                    ]);
                }
            }
        }

        echo '✅ تم إنشاء '.Harvest::count().' حصاد و '.HarvestBox::count()." صندوق بنجاح.\n";
    }
}
