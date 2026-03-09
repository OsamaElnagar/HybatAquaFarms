<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\LoanableType;
use App\Enums\RepaymentType;
use App\Models\PartnerLoanRepayment;
use Illuminate\Support\Facades\Auth;

class PartnerLoanRepaymentObserver
{
    public function __construct(
        protected PostingService $posting
    ) {}

    public function created(PartnerLoanRepayment $repayment): void
    {
        $loan = $repayment->partnerLoan;
        $loanableType = LoanableType::fromModelClass($loan->loanable_type);

        $eventKey = match ($repayment->repayment_type) {
            RepaymentType::Cash => 'partner_loan.repay_cash',
            RepaymentType::Netting => $loanableType->nettingPostingKey(),
        };

        $context = [
            'date' => $repayment->date,
            'amount' => (float) $repayment->amount,
            'description' => $repayment->description ?? "سداد سلفة - {$loan->loanable?->name}",
            'source_type' => PartnerLoanRepayment::class,
            'source_id' => $repayment->id,
            'user_id' => Auth::id() ?? $repayment->created_by,
        ];

        // For cash repayments, override the credit account with the selected treasury
        if ($repayment->repayment_type === RepaymentType::Cash) {
            $context['credit_account_id'] = $repayment->treasury_account_id;
        }

        $journalEntry = $this->posting->post($eventKey, $context);

        $repayment->update([
            'journal_entry_id' => $journalEntry->id,
        ]);
    }
}
