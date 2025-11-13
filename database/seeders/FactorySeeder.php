<?php

namespace Database\Seeders;

use App\Models\Factory;
use Illuminate\Database\Seeder;

class FactorySeeder extends Seeder
{
    public function run(): void
    {
        // Feed factories
        $feedFactories = [
            ['code' => 'FAC-HUS', 'name' => 'مصنع الحسيني للأعلاف'],
            ['code' => 'FAC-DOA', 'name' => 'مصنع الدعاء للأعلاف'],
            ['code' => 'FAC-SAL', 'name' => 'مصنع سلامة للأعلاف'],
            ['code' => 'FAC-BDR', 'name' => 'مصنع البدر للأعلاف'],
        ];

        // Batch suppliers (hatcheries)
        $hatcheries = [
            ['code' => 'HAT-ABD', 'name' => 'مفرخة عبدالله للزريعة'],
            ['code' => 'HAT-NAD', 'name' => 'مفرخة النادي للزريعة'],
            ['code' => 'HAT-ALI', 'name' => 'مفرخة علي للزريعة'],
            ['code' => 'HAT-MOH', 'name' => 'مفرخة محمد للزريعة'],
            ['code' => 'HAT-ELK', 'name' => 'مفرخة الكرمة للزريعة'],
        ];

        $allFactories = array_merge($feedFactories, $hatcheries);

        foreach ($allFactories as $factory) {
            Factory::create([
                ...$factory,
                'phone' => '01'.rand(0, 2).rand(10000000, 99999999),
                'payment_terms_days' => rand(15, 45),
                'is_active' => true,
            ]);
        }
    }
}
