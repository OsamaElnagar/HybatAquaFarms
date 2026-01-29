<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\DailyFeedIssue;
use App\Models\Farm;
use App\Models\FarmUnit;
use App\Models\FeedItem;
use App\Models\FeedWarehouse;
use Illuminate\Database\Seeder;

class DailyFeedIssueSeeder extends Seeder
{
    public function run(): void
    {
        Farm::each(function ($farm) {
            $warehouse = FeedWarehouse::where('farm_id', $farm->id)->first();
            $feedItems = FeedItem::take(2)->get();

            if (! $warehouse || $feedItems->isEmpty()) {
                return;
            }

            $units = FarmUnit::where('farm_id', $farm->id)
                ->where('status', 'active')
                ->get();

            // Create daily feed issues for last 7-14 days for each active unit
            foreach ($units->take(rand(3, 5)) as $unit) {
                $batch = Batch::whereHas('units', fn ($q) => $q->where('farm_units.id', $unit->id))->first();

                // Get available stocks for this warehouse
                $availableStocks = \App\Models\FeedStock::where('feed_warehouse_id', $warehouse->id)
                    ->whereIn('feed_item_id', $feedItems->pluck('id'))
                    ->where('quantity_in_stock', '>=', 10)
                    ->get()
                    ->keyBy('feed_item_id');

                if ($availableStocks->isEmpty()) {
                    // Skip if no stock available
                    continue;
                }

                $daysToCreate = rand(7, 14);
                $feedItemsWithStock = $feedItems->filter(fn ($item) => $availableStocks->has($item->id));

                if ($feedItemsWithStock->isEmpty()) {
                    continue;
                }

                for ($i = 1; $i <= $daysToCreate; $i++) {
                    $feedItem = $feedItemsWithStock->random();
                    $stock = $availableStocks->get($feedItem->id);

                    if (! $stock) {
                        continue;
                    }

                    // Refresh stock to get current quantity (in case previous issues reduced it)
                    $stock->refresh();
                    $availableQuantity = (float) $stock->quantity_in_stock;

                    if ($availableQuantity < 10) {
                        // Remove from available stocks if insufficient
                        $availableStocks->forget($feedItem->id);
                        if ($availableStocks->isEmpty()) {
                            break; // No more stock available
                        }

                        continue;
                    }

                    // Use a reasonable quantity (max 5% of available stock per issue)
                    $requestedQuantity = rand(10, min(50, (int) ($availableQuantity * 0.05)));
                    $quantity = min($requestedQuantity, $availableQuantity * 0.05);

                    if ($quantity < 1) {
                        continue;
                    }

                    DailyFeedIssue::create([
                        'farm_id' => $farm->id,
                        'unit_id' => $unit->id,
                        'feed_item_id' => $feedItem->id,
                        'feed_warehouse_id' => $warehouse->id,
                        'date' => now()->subDays($i),
                        'quantity' => $quantity,
                        'batch_id' => $batch?->id,
                    ]);

                    // Update available quantity in memory (approximate)
                    $stock->quantity_in_stock = $availableQuantity - $quantity;
                }
            }
        });
    }
}
