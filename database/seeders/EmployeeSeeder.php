<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Farm;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $arabicNames = [
            'محمد أحمد', 'أحمد محمود', 'محمود حسن', 'حسن علي', 'علي إبراهيم',
            'إبراهيم خالد', 'خالد عمر', 'عمر طارق', 'طارق يوسف', 'يوسف سعيد',
            'سعيد كمال', 'كمال جمال', 'جمال رضا', 'رضا فتحي', 'فتحي سامي',
        ];

        Farm::each(function ($farm) use ($arabicNames) {
            // Create 3-5 employees per farm
            for ($i = 0; $i < rand(3, 5); $i++) {
                Employee::create([
                    'employee_number' => 'EMP-'.str_pad($farm->id * 100 + $i, 6, '0', STR_PAD_LEFT),
                    'name' => $arabicNames[array_rand($arabicNames)],
                    'phone' => '01'.rand(0, 2).rand(10000000, 99999999),
                    'farm_id' => $farm->id,
                    'salary_amount' => rand(3000, 8000),

                    'hire_date' => now()->subMonths(rand(1, 36)),
                    'status' => 'active',
                ]);
            }
        });
    }
}
