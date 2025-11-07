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
                $batch = Batch::where('unit_id', $unit->id)->first();

                for ($i = 1; $i <= rand(7, 14); $i++) {
                    DailyFeedIssue::create([
                        'farm_id' => $farm->id,
                        'unit_id' => $unit->id,
                        'feed_item_id' => $feedItems->random()->id,
                        'feed_warehouse_id' => $warehouse->id,
                        'date' => now()->subDays($i),
                        'quantity' => rand(10, 50),
                        'batch_id' => $batch?->id,
                    ]);
                }
            }
        });
    }
}
