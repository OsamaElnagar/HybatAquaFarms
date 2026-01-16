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
            ['name' => 'مصنع الحسين للأعلاف'],
            ['name' => 'مصنع الدعاء للأعلاف'],
        ];

        // Batch suppliers (hatcheries)
        $hatcheries = [
            ['name' => 'مفرخة عبدالله للزريعة'],
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
