<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\FeedWarehouse;
use Illuminate\Database\Seeder;

class FeedWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Farm::each(function ($farm) {
            FeedWarehouse::create([
                'farm_id' => $farm->id,
                'code' => "{$farm->code}-WH-01",
                'name' => "مخزن أعلاف {$farm->name}",
                'location' => "داخل {$farm->location}",
                'is_active' => true,
            ]);
        });
    }
}
