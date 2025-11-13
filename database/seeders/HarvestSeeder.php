<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Harvest;
use App\Models\HarvestBox;
use App\Models\SalesOrder;
use Illuminate\Database\Seeder;

class HarvestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get batches that have been harvested or could be harvested
        $batches = Batch::whereIn('status', ['harvested', 'active'])
            ->has('unit')
            ->limit(15)
            ->get();

        foreach ($batches as $batch) {
            // Create 1-2 harvests per batch
            $harvestCount = rand(1, 2);

            for ($i = 0; $i < $harvestCount; $i++) {
                $boxesCount = rand(5, 15);
                $totalWeight = fake()->randomFloat(3, 100, 800);
                $totalQuantity = rand(200, 1500);

                // Sometimes link to sales order
                $salesOrder = rand(0, 1) ? SalesOrder::where('farm_id', $batch->farm_id)->inRandomOrder()->first() : null;

                $harvest = Harvest::create([
                    'harvest_number' => 'HRV-'.date('Y').'-'.str_pad($batch->id * 100 + $i, 5, '0', STR_PAD_LEFT),
                    'batch_id' => $batch->id,
                    'farm_id' => $batch->farm_id,
                    'unit_id' => $batch->unit_id,
                    'sales_order_id' => $salesOrder?->id,
                    'harvest_date' => fake()->dateTimeBetween('-2 months', 'now'),
                    'boxes_count' => $boxesCount,
                    'total_weight' => $totalWeight,
                    'average_weight_per_box' => $totalWeight / $boxesCount,
                    'total_quantity' => $totalQuantity,
                    'average_fish_weight' => ($totalWeight * 1000) / $totalQuantity,
                    'status' => $salesOrder ? 'sold' : fake()->randomElement(['pending', 'completed']),
                    'recorded_by' => 1,
                    'notes' => fake()->optional(0.3)->sentence(),
                ]);

                // Create boxes for this harvest
                for ($boxNum = 1; $boxNum <= $boxesCount; $boxNum++) {
                    $boxWeight = fake()->randomFloat(3, 5, $totalWeight / $boxesCount + 10);
                    $boxFishCount = rand(10, $totalQuantity / $boxesCount + 20);

                    HarvestBox::create([
                        'harvest_id' => $harvest->id,
                        'box_number' => $boxNum,
                        'weight' => $boxWeight,
                        'fish_count' => $boxFishCount,
                        'average_fish_weight' => ($boxWeight * 1000) / $boxFishCount,
                        'notes' => fake()->optional(0.2)->sentence(),
                    ]);
                }
            }
        }

        echo "تم إنشاء ".Harvest::count()." سجل حصاد بنجاح.\n";
    }
}
