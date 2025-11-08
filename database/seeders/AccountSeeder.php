<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Assets
        $assets = Account::create([
            'code' => '1000',
            'name' => 'الأصول',
            'type' => AccountType::Asset,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1100',
            'name' => 'الأصول المتداولة',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1110',
            'name' => 'النقدية بالصندوق',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1120',
            'name' => 'العُهد النقدية',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1130',
            'name' => 'مخزون الأعلاف',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1200',
            'name' => 'مخزون الزريعة',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1140',
            'name' => 'الذمم المدينة - التجار',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        Account::create([
            'code' => '1150',
            'name' => 'سلف الموظفين',
            'type' => AccountType::Asset,
            'parent_id' => $assets->id,
        ]);

        // Liabilities
        $liabilities = Account::create([
            'code' => '2000',
            'name' => 'الخصوم',
            'type' => AccountType::Liability,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '2100',
            'name' => 'الخصوم المتداولة',
            'type' => AccountType::Liability,
            'parent_id' => $liabilities->id,
        ]);

        Account::create([
            'code' => '2110',
            'name' => 'الذمم الدائنة - المصانع',
            'type' => AccountType::Liability,
            'parent_id' => $liabilities->id,
        ]);

        Account::create([
            'code' => '2120',
            'name' => 'المرتبات المستحقة',
            'type' => AccountType::Liability,
            'parent_id' => $liabilities->id,
        ]);

        // Equity
        $equity = Account::create([
            'code' => '3000',
            'name' => 'حقوق الملكية',
            'type' => AccountType::Equity,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '3100',
            'name' => 'رأس المال',
            'type' => AccountType::Equity,
            'parent_id' => $equity->id,
        ]);

        // Income
        $income = Account::create([
            'code' => '4000',
            'name' => 'الإيرادات',
            'type' => AccountType::Income,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '4100',
            'name' => 'مبيعات الأسماك',
            'type' => AccountType::Income,
            'parent_id' => $income->id,
        ]);

        // Expenses
        $expenses = Account::create([
            'code' => '5000',
            'name' => 'المصروفات',
            'type' => AccountType::Expense,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5100',
            'name' => 'تكلفة البضاعة المباعة',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5110',
            'name' => 'تكلفة الأعلاف',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5200',
            'name' => 'مصروفات التشغيل',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5210',
            'name' => 'المرتبات والأجور',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5220',
            'name' => 'الصيانة والإصلاحات',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5230',
            'name' => 'الكهرباء والماء',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5240',
            'name' => 'النقل والمواصلات',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);

        Account::create([
            'code' => '5250',
            'name' => 'مصروفات عمومية',
            'type' => AccountType::Expense,
            'parent_id' => $expenses->id,
        ]);
    }
}
