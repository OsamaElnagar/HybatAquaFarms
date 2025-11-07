<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\ClearingEntry;
use Illuminate\Support\Facades\Auth;

class ClearingEntryObserver
{
    public function __construct(
        protected PostingService $posting
    ) {}

    public function created(ClearingEntry $clearingEntry): void
    {
        // Post the settlement transaction
        $journalEntry = $this->posting->post('settlement.trader_to_factory', [
            'date' => $clearingEntry->date,
            'amount' => (float) $clearingEntry->amount,
            'description' => $clearingEntry->description ?? "تسوية بين {$clearingEntry->trader->name} و {$clearingEntry->factory->name}",
            'source_type' => ClearingEntry::class,
            'source_id' => $clearingEntry->id,
            'user_id' => Auth::id() ?? $clearingEntry->created_by,
        ]);

        // Link the journal entry to the clearing entry
        $clearingEntry->update([
            'journal_entry_id' => $journalEntry->id,
        ]);
    }
}
