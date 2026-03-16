<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\Batch;

class BatchObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(Batch $batch): void
    {
        // Only post if there's a factory and total_cost
        if ($batch->factory_id && $batch->total_cost && $batch->factory?->account_id) {
            $this->posting->post('seed.purchase', [
                'amount' => (float) $batch->total_cost,
                'farm_id' => $batch->farm_id,
                'date' => $batch->entry_date?->toDateString(),
                'source_type' => $batch->getMorphClass(),
                'source_id' => $batch->id,
                'description' => "شراء زريعة - دفعة {$batch->batch_code}",
                'credit_account_id' => $batch->factory->account_id,
                'user_id' => null, // TODO: Add created_by to batches if needed
            ]);
        }
    }

    public function updated(Batch $batch): void
    {
        // If factory_id or total_cost was added/updated, post the entry
        if ($batch->wasChanged(['factory_id', 'total_cost']) && $batch->factory_id && $batch->total_cost && $batch->factory?->account_id) {
            // Check if already posted (by checking if journal entry exists)
            $hasJournalEntry = $batch->journalEntries()->exists();

            if (! $hasJournalEntry) {
                $this->posting->post('seed.purchase', [
                    'amount' => (float) $batch->total_cost,
                    'farm_id' => $batch->farm_id,
                    'date' => $batch->entry_date?->toDateString(),
                    'source_type' => $batch->getMorphClass(),
                    'source_id' => $batch->id,
                    'description' => "شراء زريعة - دفعة {$batch->batch_code}",
                    'credit_account_id' => $batch->factory->account_id,
                    'user_id' => null,
                ]);
            }
        }
    }
}
