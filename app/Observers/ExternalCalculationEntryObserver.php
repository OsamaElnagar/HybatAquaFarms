<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\ExternalCalculationType;
use App\Models\ExternalCalculationEntry;
use Illuminate\Support\Facades\Log;

class ExternalCalculationEntryObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(ExternalCalculationEntry $entry): void
    {
        try {
            $context = [
                'amount' => (float) $entry->amount,
                'farm_id' => $entry->farm_id,
                'date' => $entry->date?->toDateString(),
                'source_type' => $entry->getMorphClass(),
                'source_id' => $entry->id,
                'description' => $entry->description,
            ];

            if ($entry->type === ExternalCalculationType::Payment) {
                $context['debit_account_id'] = $entry->account_id;
                $context['credit_account_id'] = $entry->treasury_account_id;
            } else {
                $context['debit_account_id'] = $entry->treasury_account_id;
                $context['credit_account_id'] = $entry->account_id;
            }

            $journalEntry = $this->posting->post('external.calculation', $context);

            $entry->journal_entry_id = $journalEntry->id;
            $entry->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Failed to post external calculation accounting entry: '.$e->getMessage());
        }
    }
}
