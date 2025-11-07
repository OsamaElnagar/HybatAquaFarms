<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            ['code' => 'D-001', 'name' => 'السائق محمد علي'],
            ['code' => 'D-002', 'name' => 'السائق أحمد حسن'],
            ['code' => 'D-003', 'name' => 'السائق خالد محمود'],
            ['code' => 'D-004', 'name' => 'السائق عمر سعيد'],
        ];

        foreach ($drivers as $driver) {
            Driver::create([
                ...$driver,
                'phone' => '01'.rand(0, 2).rand(10000000, 99999999),
                'license_number' => rand(10000000, 99999999),
                'license_expiry' => now()->addYears(rand(1, 5)),
                'vehicle_type' => ['نقل خفيف', 'نقل ثقيل', 'مقطورة'][rand(0, 2)],
                'vehicle_plate' => 'ن ب '.rand(1000, 9999),
                'is_active' => true,
            ]);
        }
    }
}
