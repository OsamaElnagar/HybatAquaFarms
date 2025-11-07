<?php

namespace Database\Seeders;

use App\Enums\SpeciesType;
use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        // Fish species
        Species::create([
            'name' => 'بلطي نيلي',
            'type' => SpeciesType::Fish,
            'description' => 'البلطي النيلي - النوع الأكثر شيوعاً في الاستزراع السمكي',
            'is_active' => true,
        ]);

        Species::create([
            'name' => 'بوري',
            'type' => SpeciesType::Fish,
            'description' => 'سمك البوري',
            'is_active' => true,
        ]);

        Species::create([
            'name' => 'توبار',
            'type' => SpeciesType::Fish,
            'description' => 'سمك التوبار',
            'is_active' => true,
        ]);

        Species::create([
            'name' => 'جمبري',
            'type' => SpeciesType::Fish,
            'description' => 'الجمبري',
            'is_active' => true,
        ]);

        Species::create([
            'name' => 'قراميط',
            'type' => SpeciesType::Fish,
            'description' => 'سمك القراميط',
            'is_active' => true,
        ]);

        // Animals
        Species::create([
            'name' => 'جاموس',
            'type' => SpeciesType::Animal,
            'description' => 'الجاموس',
            'is_active' => true,
        ]);

        Species::create([
            'name' => 'أغنام',
            'type' => SpeciesType::Animal,
            'description' => 'الأغنام',
            'is_active' => true,
        ]);
    }
}
