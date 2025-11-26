<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'طعام', 'code' => 'FOOD'],
            ['name' => 'مشروبات', 'code' => 'DRINKS'],
            ['name' => 'صيانة ماكينات', 'code' => 'MACHINE_REPAIR'],
            ['name' => 'راتب عمال', 'code' => 'WORKER_SALARY'],
            ['name' => 'نقل', 'code' => 'TRANSPORT'],
            ['name' => 'كهرباء/ماء', 'code' => 'UTILITIES'],
            ['name' => 'صيانة عامة', 'code' => 'MAINTENANCE'],
            ['name' => 'وقود', 'code' => 'FUEL'],
            ['name' => 'طبي', 'code' => 'MEDICAL'],
            ['name' => 'معدات', 'code' => 'EQUIPMENT'],
            ['name' => 'اتصالات', 'code' => 'COMMUNICATION'],
            ['name' => 'أخرى', 'code' => 'OTHER'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
