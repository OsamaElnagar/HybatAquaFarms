<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Seeder;

class ExternalCalculationSeeder extends Seeder
{
    public function run(): void
    {
        Account::firstOrCreate(
            ['code' => '4800'],
            [
                'name' => 'إيرادات متنوعة',
                'type' => AccountType::Income,
                'is_active' => true,
            ]
        );

        Account::firstOrCreate(
            ['code' => '5280'],
            [
                'name' => 'مصروفات متنوعة',
                'type' => AccountType::Expense,
                'is_active' => true,
            ]
        );

        PostingRule::firstOrCreate(
            ['event_key' => 'external.calculation'],
            [
                'description' => 'قيد الحسابات الخارجية (يُحدَّد الحسابان ديناميكيًا)',
                'is_active' => true,
            ]
        );
    }
}
