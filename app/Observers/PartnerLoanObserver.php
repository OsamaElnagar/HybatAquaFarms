<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\PartnerLoan;
use Illuminate\Support\Facades\Auth;

class PartnerLoanObserver
{
    public function __construct(
        protected PostingService $posting
    ) {}

    public function created(PartnerLoan $loan): void
    {
        $journalEntry = $this->posting->post('partner_loan.borrow', [
            'date' => $loan->date,
            'amount' => (float) $loan->amount,
            'debit_account_id' => $loan->treasury_account_id,
            'description' => $loan->description ?? "سلفة من {$loan->loanable?->name}",
            'source_type' => PartnerLoan::class,
            'source_id' => $loan->id,
            'user_id' => Auth::id() ?? $loan->created_by,
        ]);

        $loan->update([
            'journal_entry_id' => $journalEntry->id,
        ]);
    }
}
