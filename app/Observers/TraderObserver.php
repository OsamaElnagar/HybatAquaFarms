<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Trader;

class TraderObserver
{
    public function creating(Trader $trader): void
    {
        if (! $trader->code) {
            $trader->code = static::generateCode();
        }
    }

    public function created(Trader $trader): void
    {
        $parentAccount = Account::where('code', '1140')->first();

        if ($parentAccount) {
            $account = Account::create([
                'parent_id' => $parentAccount->id,
                'code' => '1140.'.$trader->id,
                'name' => 'تاجر: '.$trader->name,
                'type' => $parentAccount->type,
                'is_active' => true,
                'is_treasury' => false,
                'description' => 'حساب تاجر تم إنشاؤه تلقائياً',
            ]);

            $trader->account_id = $account->id;
            $trader->saveQuietly();
        }

        // Open the first statement session for this trader
        $trader->openNewStatement('كشف الحساب الأول');
    }

    protected static function generateCode(): string
    {
        $lastTrader = Trader::latest('id')->first();
        $number = $lastTrader ? ((int) substr($lastTrader->code, 4)) + 1 : 1;

        return 'TRD-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
