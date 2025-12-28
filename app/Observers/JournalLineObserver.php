<?php

namespace App\Observers;

use App\Enums\AccountType;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class JournalLineObserver
{
    public function created(JournalLine $journalLine): void
    {
        $this->updateAccountBalance($journalLine->account_id);
    }

    public function updated(JournalLine $journalLine): void
    {
        if ($journalLine->wasChanged(['debit', 'credit', 'account_id'])) {
            if ($journalLine->wasChanged('account_id')) {
                $this->updateAccountBalance($journalLine->getOriginal('account_id'));
            }
            $this->updateAccountBalance($journalLine->account_id);
        }
    }

    public function deleted(JournalLine $journalLine): void
    {
        $this->updateAccountBalance($journalLine->account_id);
    }

    protected function updateAccountBalance(int $accountId): void
    {
        $account = \App\Models\Account::find($accountId);
        if (! $account) {
            return;
        }

        // Calculate balance from scratch to ensure accuracy
        $totals = JournalLine::where('account_id', $accountId)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $debit = $totals->total_debit ?? 0;
        $credit = $totals->total_credit ?? 0;

        if (in_array($account->type, [AccountType::Asset, AccountType::Expense])) {
            $balance = $debit - $credit;
        } else {
            $balance = $credit - $debit;
        }

        // Use direct DB update to avoid triggering other observers and for speed
        DB::table('accounts')
            ->where('id', $accountId)
            ->update(['current_balance' => $balance, 'updated_at' => now()]);
    }
}
