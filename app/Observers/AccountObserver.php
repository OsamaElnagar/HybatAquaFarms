<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    public function creating(Account $account): void
    {
        if (! $account->code) {
            $account->code = static::generateCode($account);
        }
    }

    protected static function generateCode(Account $account): string
    {
        // Get the last account of the same type
        $lastAccount = Account::where('type', $account->type)
            ->latest('id')
            ->first();

        if ($lastAccount && $lastAccount->code) {
            // Extract number from code (e.g., "ACC-001" -> 1)
            preg_match('/(\d+)$/', $lastAccount->code, $matches);
            $number = isset($matches[1]) ? ((int) $matches[1]) + 1 : 1;
        } else {
            $number = 1;
        }

        // Generate code based on account type
        $prefix = match ($account->type->value) {
            'asset' => 'AST',
            'liability' => 'LIA',
            'equity' => 'EQT',
            'income' => 'INC',
            'expense' => 'EXP',
            default => 'ACC',
        };

        return $prefix.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
