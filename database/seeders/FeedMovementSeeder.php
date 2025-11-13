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
            // First, create IN movements (purchases) to build up stock
            for ($i = 0; $i < rand(5, 10); $i++) {
                $feedItem = $feedItems->random();
                $quantity = rand(500, 2000);

                $factory = $factories->random();

                FeedMovement::create([
                    'movement_type' => FeedMovementType::In,
                    'feed_item_id' => $feedItem->id,
                    'to_warehouse_id' => $warehouse->id,
                    'date' => now()->subDays(rand(30, 90)),
                    'quantity' => $quantity,
                    'factory_id' => $factory->id,
                    'description' => 'شراء أعلاف',
                ]);
            }

        });
    }
}
