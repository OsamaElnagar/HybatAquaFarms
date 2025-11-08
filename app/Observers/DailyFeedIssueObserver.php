<?php

namespace App\Observers;

use App\Enums\FeedMovementType;
use App\Models\DailyFeedIssue;
use App\Models\FeedMovement;
use Illuminate\Support\Facades\DB;

class DailyFeedIssueObserver
{
    public function created(DailyFeedIssue $issue): void
    {
        DB::transaction(function () use ($issue) {
            $this->createFeedMovement($issue);
        });
    }

    public function updated(DailyFeedIssue $issue): void
    {
        // If quantity, warehouse, or feed item changed, update the movement
        if ($issue->wasChanged(['quantity', 'feed_warehouse_id', 'feed_item_id', 'date'])) {
            DB::transaction(function () use ($issue) {
                // Find and update the associated movement
                $movement = FeedMovement::where('source_type', $issue->getMorphClass())
                    ->where('source_id', $issue->id)
                    ->first();

                if ($movement) {
                    // Delete old movement (which will revert stock)
                    $movement->delete();
                }

                // Create new movement with updated values
                $this->createFeedMovement($issue);
            });
        }
    }

    public function deleted(DailyFeedIssue $issue): void
    {
        DB::transaction(function () use ($issue) {
            // Find and delete the associated movement (which will revert stock)
            $movement = FeedMovement::where('source_type', $issue->getMorphClass())
                ->where('source_id', $issue->id)
                ->first();

            if ($movement) {
                $movement->delete();
            }
        });
    }

    protected function createFeedMovement(DailyFeedIssue $issue): void
    {
        // Get stock to calculate cost and check availability
        $stock = \App\Models\FeedStock::where('feed_warehouse_id', $issue->feed_warehouse_id)
            ->where('feed_item_id', $issue->feed_item_id)
            ->first();

        $quantity = (float) $issue->quantity;

        // Check if sufficient stock exists
        if (! $stock || (float) $stock->quantity_in_stock < $quantity) {
            throw new \Exception('Insufficient stock for daily feed issue');
        }

        $averageCost = (float) $stock->average_cost;
        $totalCost = $quantity * $averageCost;

        $unitCode = $issue->unit?->code ?? 'وحدة';
        $feedItemName = $issue->feedItem?->name ?? '';

        FeedMovement::create([
            'movement_type' => FeedMovementType::Out,
            'feed_item_id' => $issue->feed_item_id,
            'from_warehouse_id' => $issue->feed_warehouse_id,
            'to_warehouse_id' => null,
            'date' => $issue->date,
            'quantity' => $quantity,
            'unit_cost' => $averageCost,
            'total_cost' => $totalCost,
            'factory_id' => null,
            'source_type' => $issue->getMorphClass(),
            'source_id' => $issue->id,
            'description' => "صرف يومي - {$unitCode} - {$feedItemName}",
            'recorded_by' => $issue->recorded_by,
        ]);
    }
}
