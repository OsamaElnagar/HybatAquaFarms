<?php

namespace Database\Seeders;

use App\Models\FeedItem;
use App\Models\FeedStock;
use App\Models\FeedWarehouse;
use Illuminate\Database\Seeder;

class FeedStockSeeder extends Seeder
{
    public function run(): void
    {
        $feedItems = FeedItem::all();

        FeedWarehouse::each(function ($warehouse) use ($feedItems) {
            foreach ($feedItems->random(rand(2, 4)) as $feedItem) {
                $quantity = rand(500, 5000);
                $avgCost = $feedItem->standard_cost * (1 + (rand(-10, 10) / 100));

                FeedStock::create([
                    'feed_warehouse_id' => $warehouse->id,
                    'feed_item_id' => $feedItem->id,
                    'quantity_in_stock' => $quantity,
                    'average_cost' => $avgCost,
                    'total_value' => $quantity * $avgCost,
                ]);
            }
        });
    }
}
