<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Console\Command;

class DebugAccounting extends Command
{
    protected $signature = 'debug:accounting {--farm=}';

    protected $description = 'Show high-level accounting configuration: accounts and posting rules';

    public function handle(): int
    {
        $this->info('=== Accounts (key ones) ===');

        $query = Account::query()->orderBy('code');

        if ($this->option('farm')) {
            $query->where('farm_id', $this->option('farm'));
        }

        $accounts = $query->get(['id', 'code', 'name', 'type', 'is_treasury', 'current_balance']);

        $this->table(
            ['ID', 'Code', 'Name', 'Type', 'Treasury', 'Current Balance'],
            $accounts->map(function ($a) {
                return [
                    $a->id,
                    $a->code,
                    $a->name,
                    (string) $a->type?->value ?? $a->type,
                    $a->is_treasury ? 'yes' : 'no',
                    number_format((float) $a->current_balance, 2),
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('=== Posting Rules ===');

        $rules = PostingRule::with(['debitAccount', 'creditAccount'])
            ->orderBy('event_key')
            ->get();

        $this->table(
            ['Event Key', 'Description', 'Debit (code - name)', 'Credit (code - name)', 'Active'],
            $rules->map(function ($rule) {
                $debit = $rule->debitAccount;
                $credit = $rule->creditAccount;

                return [
                    $rule->event_key,
                    $rule->description,
                    $debit ? "{$debit->code} - {$debit->name}" : $rule->debit_account_id,
                    $credit ? "{$credit->code} - {$credit->name}" : $rule->credit_account_id,
                    $rule->is_active ? 'yes' : 'no',
                ];
            })->toArray()
        );

        return static::SUCCESS;
    }
}
