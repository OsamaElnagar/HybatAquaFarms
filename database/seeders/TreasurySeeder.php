<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\PettyCash;
use Illuminate\Database\Seeder;

class TreasurySeeder extends Seeder
{
    public function run(): void
    {
        // Mark cash accounts as is_cash = true based on code patterns
        Account::where('code', 'like', '111%')
               ->orWhere('code', '1120') // Petty cash specifically
               ->update(['is_cash' => true]);

        // Link PettyCashes to a cash account (use first matching '1120' or create farm-specific logic if accounts are farm-scoped)
        $pettyCashAccount = Account::where('code', '1120')->first();
        if ($pettyCashAccount) {
            PettyCash::with('farm')->get()->each(function ($pettyCash) use ($pettyCashAccount) {
                $pettyCash->update(['account_id' => $pettyCashAccount->id]);
            });
        }
    }
}