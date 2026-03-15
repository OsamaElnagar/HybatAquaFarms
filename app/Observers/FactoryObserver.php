<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Factory;

class FactoryObserver
{
    public function creating(Factory $factory): void
    {
        if (! $factory->code) {
            $factory->code = static::generateCode();
        }
    }

    public function created(Factory $factory): void
    {
        $parentAccount = Account::where('code', '2110')->first();

        if ($parentAccount) {
            $account = Account::create([
                'parent_id' => $parentAccount->id,
                'code' => '2110.'.$factory->id,
                'name' => 'مصنع: '.$factory->name,
                'type' => $parentAccount->type,
                'is_active' => true,
                'is_treasury' => false,
                'description' => 'حساب مصنع تم إنشاؤه تلقائياً',
            ]);

            $factory->account_id = $account->id;
            $factory->saveQuietly();
        }

        // Open the first statement session for this factory
        $factory->openNewStatement('كشف الحساب الأول');
    }

    protected static function generateCode(): string
    {
        $lastFactory = Factory::latest('id')->first();
        $number = $lastFactory ? ((int) substr($lastFactory->code, 4)) + 1 : 1;

        return 'FAC-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
