<?php

namespace Database\Seeders;

use App\Models\Trader;
use Illuminate\Database\Seeder;

class TraderSeeder extends Seeder
{
    public function run(): void
    {
        $traders = [
            ['code' => 'T-001', 'name' => 'تاجر الإسكندرية - محمد السيد'],
            ['code' => 'T-002', 'name' => 'تاجر القاهرة - أحمد حسن'],
            ['code' => 'T-003', 'name' => 'تاجر المنصورة - خالد محمود'],
            ['code' => 'T-004', 'name' => 'تاجر طنطا - عمر إبراهيم'],
            ['code' => 'T-005', 'name' => 'تاجر الفيوم - سعيد علي'],
            ['code' => 'T-006', 'name' => 'تاجر بني سويف - جمال رضا'],
        ];

        foreach ($traders as $trader) {
            Trader::create([
                ...$trader,
                'phone' => '01'.rand(0, 2).rand(10000000, 99999999),
                'phone2' => '01'.rand(0, 2).rand(10000000, 99999999),
                'trader_type' => rand(1, 2) === 1 ? 'wholesale' : 'retail',
                'payment_terms_days' => rand(0, 30),
                'credit_limit' => rand(50000, 500000),
                'commission_rate' => fake()->randomElement([1.5, 2.0, 2.5, 3.0, 3.5]),
                'commission_type' => 'percentage',
                'default_transport_cost_per_kg' => fake()->randomFloat(2, 0.5, 2.0),
                'default_transport_cost_flat' => fake()->randomFloat(2, 50, 200),
                'is_active' => true,
            ]);
        }
    }
}
