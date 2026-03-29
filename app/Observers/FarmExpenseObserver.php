<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\FarmExpenseType;
use App\Models\FarmExpense;
use Illuminate\Support\Facades\Log;

class FarmExpenseObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(FarmExpense $farmExpense): void
    {
        try {
            $context = [
                'amount' => (float) $farmExpense->amount,
                'farm_id' => $farmExpense->farm_id,
                'date' => $farmExpense->date?->toDateString(),
                'source_type' => $farmExpense->getMorphClass(),
                'source_id' => $farmExpense->id,
                'description' => $farmExpense->description,
            ];

            if ($farmExpense->type === FarmExpenseType::Expense) {
                $context['debit_account_id'] = $farmExpense->account_id;
                $context['credit_account_id'] = $farmExpense->treasury_account_id;
            } else {
                $context['debit_account_id'] = $farmExpense->treasury_account_id;
                $context['credit_account_id'] = $farmExpense->account_id;
            }

            // If this is an employee settlement, link it to their statement
            if ($farmExpense->advance_repayment_id) {
                $repayment = \App\Models\AdvanceRepayment::find($farmExpense->advance_repayment_id);
                $employee = $repayment?->employeeAdvance?->employee;
                $context['employee_statement_id'] = $employee?->active_statement?->id;
            }

            $journalEntry = $this->posting->post('farm.expense', $context);

            $farmExpense->journal_entry_id = $journalEntry->id;
            $farmExpense->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Failed to post farm expense accounting entry: '.$e->getMessage());
        }
    }
}
