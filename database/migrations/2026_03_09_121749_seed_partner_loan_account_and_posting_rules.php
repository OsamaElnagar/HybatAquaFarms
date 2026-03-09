<?php

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Create Partner Loans Payable account (2130)
        $liabilities = Account::where('code', '2000')->first();

        if ($liabilities) {
            Account::firstOrCreate(
                ['code' => '2130'],
                [
                    'name' => 'سلف الشركاء',
                    'type' => AccountType::Liability,
                    'parent_id' => $liabilities->id,
                    'is_treasury' => false,
                ]
            );
        }

        // Create posting rules for partner loans
        $rules = [
            ['event_key' => 'partner_loan.borrow', 'debit' => '1110', 'credit' => '2130', 'description' => 'استلام سلفة من شريك'],
            ['event_key' => 'partner_loan.repay_cash', 'debit' => '2130', 'credit' => '1110', 'description' => 'سداد سلفة نقدي'],
            ['event_key' => 'partner_loan.repay_netting_trader', 'debit' => '2130', 'credit' => '1140', 'description' => 'مقاصة سلفة من مبيعات تاجر'],
            ['event_key' => 'partner_loan.repay_netting_factory', 'debit' => '2130', 'credit' => '2110', 'description' => 'مقاصة سلفة من مستحقات مصنع'],
        ];

        foreach ($rules as $rule) {
            $debitAccount = Account::where('code', $rule['debit'])->first();
            $creditAccount = Account::where('code', $rule['credit'])->first();

            if ($debitAccount && $creditAccount) {
                PostingRule::firstOrCreate(
                    ['event_key' => $rule['event_key']],
                    [
                        'debit_account_id' => $debitAccount->id,
                        'credit_account_id' => $creditAccount->id,
                        'description' => $rule['description'],
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        PostingRule::whereIn('event_key', [
            'partner_loan.borrow',
            'partner_loan.repay_cash',
            'partner_loan.repay_netting_trader',
            'partner_loan.repay_netting_factory',
        ])->delete();

        Account::where('code', '2130')->delete();
    }
};
