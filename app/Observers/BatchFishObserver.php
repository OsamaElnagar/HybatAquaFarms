<?php

namespace App\Observers;

use App\Models\BatchFish;

class BatchFishObserver
{
    /**
     * Handle the BatchFish "created" event.
     */
    public function created(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
    }

    public function updated(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
    }

    public function deleted(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
    }

    protected function syncBatch($batch): void
    {
        if (! $batch) {
            return;
        }

        $fish = $batch->fish()->get();
        $totalQuantity = $fish->sum('quantity');
        $totalCost = $fish->sum('total_cost');

        // Note: For current_quantity, we might need to handle mortality/harvests.
        // For now, we update it to match initial_quantity if it was a new batch,
        // or just maintain the delta. However, simple approach for now:
        $batch->updateQuietly([
            'initial_quantity' => $totalQuantity,
            'current_quantity' => $totalQuantity, // Assuming starting point
            'total_cost' => $totalCost,
        ]);
    }

    /**
     * Handle the BatchFish "restored" event.
     */
    public function restored(BatchFish $batchFish): void
    {
        //
    }

    /**
     * Handle the BatchFish "force deleted" event.
     */
    public function forceDeleted(BatchFish $batchFish): void
    {
        //
    }
}
