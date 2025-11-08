<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\FeedMovementType;
use App\Models\FeedMovement;
use App\Models\FeedStock;
use App\Models\FeedWarehouse;
use Illuminate\Support\Facades\DB;

class FeedMovementObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(FeedMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $this->updateStocks($movement);
            $this->postAccountingEntry($movement);
        });
    }

    public function updated(FeedMovement $movement): void
    {
        // If quantity, warehouse, or movement type changed, recalculate stocks
        if ($movement->wasChanged(['quantity', 'from_warehouse_id', 'to_warehouse_id', 'movement_type', 'unit_cost', 'total_cost'])) {
            DB::transaction(function () use ($movement) {
                // Revert old movement (stocks only, no accounting)
                $this->revertStockUpdateOnly($movement);
                // Apply new movement (stocks only, no accounting)
                $this->updateStocksOnly($movement);
                // Re-post accounting entry if cost or type changed
                // Note: This might create duplicate entries - consider deleting old journal entries first
                if ($movement->wasChanged(['total_cost', 'movement_type']) && $movement->total_cost > 0) {
                    $this->postAccountingEntry($movement);
                }
            });
        }
    }

    public function deleted(FeedMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $this->revertStockUpdate($movement);
        });
    }

    protected function updateStocks(FeedMovement $movement): void
    {
        $this->updateStocksOnly($movement);
    }

    protected function updateStocksOnly(FeedMovement $movement): void
    {
        match ($movement->movement_type) {
            FeedMovementType::In => $this->handleInMovement($movement),
            FeedMovementType::Out => $this->handleOutMovement($movement),
            FeedMovementType::Transfer => $this->handleTransferMovement($movement),
        };
    }

    protected function handleInMovement(FeedMovement $movement): void
    {
        if (! $movement->to_warehouse_id) {
            return;
        }

        $stock = FeedStock::firstOrCreate(
            [
                'feed_warehouse_id' => $movement->to_warehouse_id,
                'feed_item_id' => $movement->feed_item_id,
            ],
            [
                'quantity_in_stock' => 0,
                'average_cost' => 0,
                'total_value' => 0,
            ]
        );

        $quantity = (float) $movement->quantity;
        $unitCost = (float) ($movement->unit_cost ?? 0);
        $totalCost = (float) ($movement->total_cost ?? ($quantity * $unitCost));

        // Calculate weighted average cost
        $currentQuantity = (float) $stock->quantity_in_stock;
        $currentValue = (float) $stock->total_value;
        $newQuantity = $currentQuantity + $quantity;
        $newValue = $currentValue + $totalCost;

        $averageCost = $newQuantity > 0 ? $newValue / $newQuantity : 0;

        $stock->update([
            'quantity_in_stock' => $newQuantity,
            'average_cost' => $averageCost,
            'total_value' => $newValue,
        ]);

        // Update movement total_cost if not set
        if (! $movement->total_cost && $unitCost > 0) {
            $movement->update(['total_cost' => $totalCost]);
        }
    }

    protected function handleOutMovement(FeedMovement $movement): void
    {
        if (! $movement->from_warehouse_id) {
            return;
        }

        $stock = FeedStock::where('feed_warehouse_id', $movement->from_warehouse_id)
            ->where('feed_item_id', $movement->feed_item_id)
            ->first();

        if (! $stock || (float) $stock->quantity_in_stock < (float) $movement->quantity) {
            throw new \Exception('Insufficient stock for feed movement');
        }

        $quantity = (float) $movement->quantity;
        $averageCost = (float) $stock->average_cost;
        $unitCost = (float) ($movement->unit_cost ?? $averageCost);
        $totalCost = (float) ($movement->total_cost ?? ($quantity * $unitCost));

        $currentQuantity = (float) $stock->quantity_in_stock;
        $currentValue = (float) $stock->total_value;
        $newQuantity = $currentQuantity - $quantity;
        $newValue = $currentValue - ($quantity * $averageCost);

        $stock->update([
            'quantity_in_stock' => max(0, $newQuantity),
            'total_value' => max(0, $newValue),
            // Average cost remains the same for out movements
        ]);

        // Update movement costs if not set
        if (! $movement->unit_cost) {
            $movement->update(['unit_cost' => $averageCost]);
        }
        if (! $movement->total_cost) {
            $movement->update(['total_cost' => $totalCost]);
        }
    }

    protected function handleTransferMovement(FeedMovement $movement): void
    {
        if (! $movement->from_warehouse_id || ! $movement->to_warehouse_id) {
            return;
        }

        // First, handle the out movement from source warehouse
        $fromStock = FeedStock::where('feed_warehouse_id', $movement->from_warehouse_id)
            ->where('feed_item_id', $movement->feed_item_id)
            ->first();

        if (! $fromStock || (float) $fromStock->quantity_in_stock < (float) $movement->quantity) {
            throw new \Exception('Insufficient stock for feed transfer');
        }

        $quantity = (float) $movement->quantity;
        $averageCost = (float) $fromStock->average_cost;
        $transferValue = $quantity * $averageCost;

        // Update from warehouse stock
        $fromQuantity = (float) $fromStock->quantity_in_stock;
        $fromValue = (float) $fromStock->total_value;
        $fromStock->update([
            'quantity_in_stock' => max(0, $fromQuantity - $quantity),
            'total_value' => max(0, $fromValue - $transferValue),
        ]);

        // Then, handle the in movement to destination warehouse
        $toStock = FeedStock::firstOrCreate(
            [
                'feed_warehouse_id' => $movement->to_warehouse_id,
                'feed_item_id' => $movement->feed_item_id,
            ],
            [
                'quantity_in_stock' => 0,
                'average_cost' => 0,
                'total_value' => 0,
            ]
        );

        $toQuantity = (float) $toStock->quantity_in_stock;
        $toValue = (float) $toStock->total_value;
        $newToQuantity = $toQuantity + $quantity;
        $newToValue = $toValue + $transferValue;

        $newAverageCost = $newToQuantity > 0 ? $newToValue / $newToQuantity : 0;

        $toStock->update([
            'quantity_in_stock' => $newToQuantity,
            'average_cost' => $newAverageCost,
            'total_value' => $newToValue,
        ]);

        // Update movement costs if not set
        if (! $movement->unit_cost) {
            $movement->update(['unit_cost' => $averageCost]);
        }
        if (! $movement->total_cost) {
            $movement->update(['total_cost' => $transferValue]);
        }
    }

    protected function revertStockUpdate(FeedMovement $movement): void
    {
        $this->revertStockUpdateOnly($movement);
    }

    protected function revertStockUpdateOnly(FeedMovement $movement): void
    {
        // Get original values before update (if updating)
        $originalQuantity = $movement->getOriginal('quantity') ?? $movement->quantity;
        $originalFromWarehouse = $movement->getOriginal('from_warehouse_id') ?? $movement->from_warehouse_id;
        $originalToWarehouse = $movement->getOriginal('to_warehouse_id') ?? $movement->to_warehouse_id;
        $originalTypeValue = $movement->getOriginal('movement_type');

        // Handle both enum instance and string value
        if ($originalTypeValue instanceof FeedMovementType) {
            $originalType = $originalTypeValue;
        } elseif (is_string($originalTypeValue)) {
            $originalType = FeedMovementType::from($originalTypeValue);
        } else {
            $originalType = $movement->movement_type;
        }

        // Create a temporary movement with original values to reverse (stocks only)
        $reversalMovement = new FeedMovement;
        $reversalMovement->movement_type = match ($originalType) {
            FeedMovementType::In => FeedMovementType::Out,
            FeedMovementType::Out => FeedMovementType::In,
            FeedMovementType::Transfer => FeedMovementType::Transfer,
        };
        $reversalMovement->feed_item_id = $movement->feed_item_id;
        // Swap warehouses to reverse the movement
        $reversalMovement->from_warehouse_id = $originalToWarehouse;
        $reversalMovement->to_warehouse_id = $originalFromWarehouse;
        $reversalMovement->quantity = $originalQuantity;
        $reversalMovement->unit_cost = $movement->getOriginal('unit_cost') ?? $movement->unit_cost;
        $reversalMovement->total_cost = $movement->getOriginal('total_cost') ?? $movement->total_cost;

        // Reverse the movement (stocks only, no accounting)
        $this->updateStocksOnly($reversalMovement);
    }

    protected function postAccountingEntry(FeedMovement $movement): void
    {
        $warehouse = $movement->toWarehouse ?? $movement->fromWarehouse;
        $farmId = $warehouse instanceof FeedWarehouse ? $warehouse->farm_id : null;

        if ($movement->movement_type === FeedMovementType::In && $movement->total_cost > 0) {
            $this->posting->post('feed.purchase', [
                'amount' => (float) $movement->total_cost,
                'farm_id' => $farmId,
                'date' => $movement->date?->toDateString(),
                'source_type' => $movement->getMorphClass(),
                'source_id' => $movement->id,
                'description' => $movement->description,
            ]);
        } elseif ($movement->movement_type === FeedMovementType::Out && $movement->total_cost > 0) {
            $this->posting->post('feed.issue', [
                'amount' => (float) $movement->total_cost,
                'farm_id' => $farmId,
                'date' => $movement->date?->toDateString(),
                'source_type' => $movement->getMorphClass(),
                'source_id' => $movement->id,
                'description' => $movement->description,
            ]);
        }
    }
}
