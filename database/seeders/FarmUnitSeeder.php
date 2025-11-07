<?php

namespace Database\Seeders;

use App\Enums\UnitType;
use App\Models\Farm;
use App\Models\FarmUnit;
use Illuminate\Database\Seeder;

class FarmUnitSeeder extends Seeder
{
    public function run(): void
    {
        Farm::each(function ($farm) {
            // Create 3-8 ponds per farm
            $pondCount = rand(3, 8);

            for ($i = 1; $i <= $pondCount; $i++) {
                FarmUnit::create([
                    'farm_id' => $farm->id,
                    'code' => "حوض-{$i}",
                    'unit_type' => UnitType::Pond,
                    'capacity' => rand(5000, 20000),
                    'status' => rand(1, 10) > 2 ? 'active' : 'maintenance',
                ]);
            }

            // Add 1-2 tanks for some farms
            if (rand(1, 3) === 1) {
                FarmUnit::create([
                    'farm_id' => $farm->id,
                    'code' => 'خزان-1',
                    'unit_type' => UnitType::Tank,
                    'capacity' => rand(2000, 5000),
                    'status' => 'active',
                ]);
            }
        });
    }
}
