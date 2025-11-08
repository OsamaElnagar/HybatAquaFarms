<?php

namespace App\Observers;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\BatchMovement;

class BatchMovementObserver
{
    public function created(BatchMovement $movement): void
    {
        $batch = $movement->batch;

        if (! $batch) {
            return;
        }

        // Update batch quantities based on movement type
        match ($movement->movement_type) {
            MovementType::Entry => $this->handleEntry($batch, $movement),
            MovementType::Transfer => $this->handleTransfer($batch, $movement),
            MovementType::Harvest => $this->handleHarvest($batch, $movement),
            MovementType::Mortality => $this->handleMortality($batch, $movement),
        };

        // Update batch location if it's a transfer
        if ($movement->movement_type === MovementType::Transfer && $movement->to_farm_id && $movement->to_unit_id) {
            $batch->update([
                'farm_id' => $movement->to_farm_id,
                'unit_id' => $movement->to_unit_id,
            ]);
        }

        // Update batch status if needed
        if ($batch->current_quantity <= 0 && $batch->status->value !== 'depleted') {
            $batch->update(['status' => \App\Enums\BatchStatus::Depleted]);
        }
    }

    public function updated(BatchMovement $movement): void
    {
        // If quantity or movement type changed, recalculate batch quantities
        if ($movement->wasChanged(['quantity', 'movement_type'])) {
            // Recalculate all movements for this batch
            $this->recalculateBatchQuantities($movement->batch);
        }
    }

    public function deleted(BatchMovement $movement): void
    {
        // Recalculate batch quantities after deletion
        if ($movement->batch) {
            $this->recalculateBatchQuantities($movement->batch);
        }
    }

    protected function handleEntry(Batch $batch, BatchMovement $movement): void
    {
        // Entry increases current quantity
        $batch->increment('current_quantity', $movement->quantity);
    }

    protected function handleTransfer(Batch $batch, BatchMovement $movement): void
    {
        // Transfer decreases quantity from source batch
        // Note: This assumes the batch is being transferred FROM
        // If transferring TO, you might need to handle it differently
        $batch->decrement('current_quantity', $movement->quantity);
    }

    protected function handleHarvest(Batch $batch, BatchMovement $movement): void
    {
        // Harvest decreases current quantity
        $batch->decrement('current_quantity', $movement->quantity);

        // Update status if all harvested
        if ($batch->current_quantity <= 0) {
            $batch->update(['status' => \App\Enums\BatchStatus::Harvested]);
        }
    }

    protected function handleMortality(Batch $batch, BatchMovement $movement): void
    {
        // Mortality decreases current quantity
        $batch->decrement('current_quantity', $movement->quantity);
    }

    protected function recalculateBatchQuantities(Batch $batch): void
    {
        // Reset to initial quantity
        $currentQuantity = $batch->initial_quantity;

        // Recalculate based on all movements
        foreach ($batch->movements()->orderBy('date')->orderBy('id')->get() as $movement) {
            match ($movement->movement_type) {
                MovementType::Entry => $currentQuantity += $movement->quantity,
                MovementType::Transfer, MovementType::Harvest, MovementType::Mortality => $currentQuantity -= $movement->quantity,
            };
        }

        // Ensure quantity doesn't go negative
        $currentQuantity = max(0, $currentQuantity);

        $batch->update(['current_quantity' => $currentQuantity]);
    }
}
