<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Food', 'name_arabic' => 'طعام', 'code' => 'FOOD'],
            ['name' => 'Drinks', 'name_arabic' => 'مشروبات', 'code' => 'DRINKS'],
            ['name' => 'Machine Repair', 'name_arabic' => 'صيانة ماكينات', 'code' => 'MACHINE_REPAIR'],
            ['name' => 'Worker Salary', 'name_arabic' => 'راتب عمال', 'code' => 'WORKER_SALARY'],
            ['name' => 'Transportation', 'name_arabic' => 'نقل', 'code' => 'TRANSPORT'],
            ['name' => 'Utilities', 'name_arabic' => 'كهرباء/ماء', 'code' => 'UTILITIES'],
            ['name' => 'Maintenance', 'name_arabic' => 'صيانة عامة', 'code' => 'MAINTENANCE'],
            ['name' => 'Fuel', 'name_arabic' => 'وقود', 'code' => 'FUEL'],
            ['name' => 'Medical', 'name_arabic' => 'طبي', 'code' => 'MEDICAL'],
            ['name' => 'Equipment', 'name_arabic' => 'معدات', 'code' => 'EQUIPMENT'],
            ['name' => 'Communication', 'name_arabic' => 'اتصالات', 'code' => 'COMMUNICATION'],
            ['name' => 'Other', 'name_arabic' => 'أخرى', 'code' => 'OTHER'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'name_arabic' => $category['name_arabic'],
                    'is_active' => true,
                ]
            );
        }
    }
}
