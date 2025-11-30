<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Seeder;

class PostingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $id = fn (string $code) => (int) Account::query()->where('code', $code)->value('id');

        $rules = [
            ['event_key' => 'voucher.payment', 'debit' => '5000', 'credit' => '1120', 'description' => 'صرف من العهدة - مصروف'],
            ['event_key' => 'voucher.receipt', 'debit' => '1110', 'credit' => '4000', 'description' => 'قبض نقدي - إيرادات'],

            ['event_key' => 'sales.cash', 'debit' => '1110', 'credit' => '4100', 'description' => 'مبيعات نقدية'],
            ['event_key' => 'sales.credit', 'debit' => '1140', 'credit' => '4100', 'description' => 'مبيعات آجلة'],
            ['event_key' => 'sales.payment', 'debit' => '1110', 'credit' => '1140', 'description' => 'تحصيل من المبيعات الآجلة'],

            ['event_key' => 'feed.purchase', 'debit' => '1130', 'credit' => '2110', 'description' => 'شراء أعلاف من مصنع'],
            ['event_key' => 'feed.issue', 'debit' => '5100', 'credit' => '1130', 'description' => 'صرف أعلاف للأحواض'],

            ['event_key' => 'seed.purchase', 'debit' => '1200', 'credit' => '2110', 'description' => 'شراء زريعة من مفرخة'],

            ['event_key' => 'employee.advance', 'debit' => '1150', 'credit' => '1120', 'description' => 'سُلفة موظف من العهدة'],
            ['event_key' => 'employee.advance.repayment', 'debit' => '5210', 'credit' => '1150', 'description' => 'خصم سُلفة من المرتب'],

            ['event_key' => 'settlement.trader_to_factory', 'debit' => '2110', 'credit' => '1140', 'description' => 'تسوية تاجر↔مصنع'],

            ['event_key' => 'factory.payment', 'debit' => '2110', 'credit' => '1110', 'description' => 'دفعة لمصنع أعلاف'],
            ['event_key' => 'batch.payment', 'debit' => '2110', 'credit' => '1110', 'description' => 'دفعة لزريعة'],
        ];

        foreach ($rules as $rule) {
            PostingRule::query()->updateOrCreate(
                ['event_key' => $rule['event_key']],
                [
                    'description' => $rule['description'],
                    'debit_account_id' => $id($rule['debit']),
                    'credit_account_id' => $id($rule['credit']),
                    'options' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
