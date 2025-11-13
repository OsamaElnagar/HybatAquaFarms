<?php

namespace Database\Seeders;

use App\Enums\FarmStatus;
use App\Models\Farm;
use Illuminate\Database\Seeder;

class FarmSeeder extends Seeder
{
    public function run(): void
    {
        $farms = [
            ['code' => 'SAYNA-150', 'name' => 'مزرعة سيناء 150', 'size' => 150, 'location' => 'سيناء'],
            ['code' => 'SAYNA-130', 'name' => 'مزرعة سيناء 130', 'size' => 130, 'location' => 'سيناء'],
            ['code' => 'SAYNA-90', 'name' => 'مزرعة سيناء 90', 'size' => 90, 'location' => 'سيناء'],
            ['code' => 'SAYNA-50', 'name' => 'مزرعة سيناء 50', 'size' => 50, 'location' => 'سيناء'],
            ['code' => 'SAYNA-25', 'name' => 'مزرعة سيناء 25', 'size' => 25, 'location' => 'سيناء'],
            ['code' => 'DAMRO-40', 'name' => 'مزرعة دمرو 40', 'size' => 40, 'location' => 'دمرو'],
            ['code' => 'DAMRO-35', 'name' => 'مزرعة دمرو 35', 'size' => 35, 'location' => 'دمرو'],
            ['code' => 'AMREIA', 'name' => 'مزرعة العامرية', 'size' => 75, 'location' => 'العامرية'],
        ];

        foreach ($farms as $farmData) {
            Farm::create([
                ...$farmData,
                'status' => FarmStatus::Active,
                'established_date' => now()->subYears(rand(2, 8)),
            ]);
        }
    }
}
