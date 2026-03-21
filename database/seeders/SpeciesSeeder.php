<?php

namespace Database\Seeders;

use App\Enums\SpeciesType;
use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $species = [
            // Fish species
            [
                'name' => 'بلطي نيلي',
                'type' => SpeciesType::Fish,
                'description' => 'البلطي النيلي - النوع الأكثر شيوعاً في الاستزراع السمكي',
            ],
            [
                'name' => 'بوري',
                'type' => SpeciesType::Fish,
                'description' => 'سمك البوري',
            ],
            [
                'name' => 'توبار',
                'type' => SpeciesType::Fish,
                'description' => 'سمك التوبار',
            ],
            [
                'name' => 'جمبري',
                'type' => SpeciesType::Fish,
                'description' => 'الجمبري',
            ],
            [
                'name' => 'قراميط',
                'type' => SpeciesType::Fish,
                'description' => 'سمك القراميط',
            ],

            // Animals
            [
                'name' => 'جاموس',
                'type' => SpeciesType::Animal,
                'description' => 'الجاموس',
            ],
            [
                'name' => 'أغنام',
                'type' => SpeciesType::Animal,
                'description' => 'الأغنام',
            ],

            // Poultry
            [
                'name' => 'دجاج (تسمين)',
                'type' => SpeciesType::Poultry,
                'description' => 'دجاج التسمين (لاحم)',
            ],
            [
                'name' => 'دجاج (بياض)',
                'type' => SpeciesType::Poultry,
                'description' => 'دجاج بياض (إنتاج بيض)',
            ],
            [
                'name' => 'ديك رومي',
                'type' => SpeciesType::Poultry,
                'description' => 'ديك رومي',
            ],
            [
                'name' => 'بط',
                'type' => SpeciesType::Poultry,
                'description' => 'البط',
            ],
        ];

        foreach ($species as $item) {
            Species::updateOrCreate(
                ['name' => $item['name']],
                [
                    'type' => $item['type'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
