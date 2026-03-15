<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\BatchFish;

class BatchFishObserver
{
    public function __construct(protected PostingService $posting) {}

    /**
     * Handle the BatchFish "created" event.
     */
    public function created(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
        $this->postAccountingEntry($batchFish);
    }

    public function updated(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
        // For simplicity, we only post on creation for now.
        // In a full implementation, we would reverse and re-post or update the JE.
    }

    public function deleted(BatchFish $batchFish): void
    {
        $this->syncBatch($batchFish->batch);
    }

    protected function postAccountingEntry(BatchFish $batchFish): void
    {
        $factory = $batchFish->factory;
        if (! $factory) {
            return;
        }

        $activeStatement = $factory->activeStatement;

        try {
            $this->posting->post('seed.purchase', [
                'amount' => (float) $batchFish->total_cost,
                'farm_id' => $batchFish->batch?->farm_id,
                'date' => $batchFish->created_at?->toDateString() ?? now()->toDateString(),
                'source_type' => $batchFish->getMorphClass(),
                'source_id' => $batchFish->id,
                'factory_statement_id' => $activeStatement?->id,
                'description' => "شراء زريعة - {$batchFish->batch?->batch_code} - {$factory->name}",
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to post seed purchase accounting entry: '.$e->getMessage());
        }
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
