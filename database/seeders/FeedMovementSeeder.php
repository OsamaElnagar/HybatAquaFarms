<?php

namespace Database\Seeders;

use App\Enums\FeedMovementType;
use App\Models\Factory;
use App\Models\FeedItem;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;
use Illuminate\Database\Seeder;

class FeedMovementSeeder extends Seeder
{
    public function run(): void
    {
        $feedItems = FeedItem::all();
        $factories = Factory::all();

        FeedWarehouse::each(function ($warehouse) use ($feedItems, $factories) {
            // Create 5-10 IN movements (purchases)
            for ($i = 0; $i < rand(5, 10); $i++) {
                $feedItem = $feedItems->random();
                $quantity = rand(500, 2000);
                $unitCost = $feedItem->standard_cost * (1 + (rand(-5, 5) / 100));

                $factory = $factories->random();

                FeedMovement::create([
                    'movement_type' => FeedMovementType::In,
                    'feed_item_id' => $feedItem->id,
                    'to_warehouse_id' => $warehouse->id,
                    'date' => now()->subDays(rand(1, 90)),
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                    'factory_id' => $factory->id,
                    'source_type' => get_class($factory),
                    'source_id' => $factory->id,
                    'description' => 'شراء أعلاف',
                ]);
            }

            // Create 3-6 OUT movements (consumption)
            for ($i = 0; $i < rand(3, 6); $i++) {
                $feedItem = $feedItems->random();
                $quantity = rand(100, 500);

                FeedMovement::create([
                    'movement_type' => FeedMovementType::Out,
                    'feed_item_id' => $feedItem->id,
                    'from_warehouse_id' => $warehouse->id,
                    'date' => now()->subDays(rand(1, 60)),
                    'quantity' => $quantity,
                    'source_type' => FeedWarehouse::class,
                    'source_id' => $warehouse->id,
                    'description' => 'صرف للأحواض',
                ]);
            }
        });
    }
}
