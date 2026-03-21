<?php

namespace Database\Seeders;

use App\Enums\SpeciesType;
use App\Models\Box;
use App\Models\Species;
use Illuminate\Database\Seeder;

class BoxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $speciesList = Species::all();

        if ($speciesList->isEmpty()) {
            // Fallback if no species, though SpeciesSeeder should run first
            return;
        }

        $fishBoxTypes = [
            ['name' => 'فوم جامبو', 'max_weight' => 25, 'class' => 'جامبو', 'category' => 'فوم'],
            ['name' => 'فوم نمرة 1', 'max_weight' => 24, 'class' => '1', 'category' => 'فوم'],
            ['name' => 'فوم نمرة 2', 'max_weight' => 23, 'class' => '2', 'category' => 'فوم'],
            ['name' => 'فوم نمرة 3', 'max_weight' => 22, 'class' => '3', 'category' => 'فوم'],
            ['name' => 'بلاستيك جامبو', 'max_weight' => 20, 'class' => 'جامبو', 'category' => 'بلاستيك'],
            ['name' => 'بلاستيك نمرة 1', 'max_weight' => 20, 'class' => '1', 'category' => 'بلاستيك'],
        ];

        $poultryBoxTypes = [
            ['name' => 'طبق بيض', 'max_weight' => 2, 'class' => '30 بيضة', 'category' => 'طبق'],
            ['name' => 'صندوق بيض', 'max_weight' => 24, 'class' => '12 طبق', 'category' => 'صندوق'],
            ['name' => 'فرخة مفرد', 'max_weight' => 2, 'class' => 'حبة', 'category' => 'وزن'],
        ];

        foreach ($speciesList as $species) {
            $types = ($species->type === SpeciesType::Poultry) ? $poultryBoxTypes : $fishBoxTypes;

            foreach ($types as $type) {
                Box::firstOrCreate(
                    [
                        'name' => $type['name'].' - '.$species->name,
                        'species_id' => $species->id,
                    ],
                    [
                        'max_weight' => $type['max_weight'],
                        'class_total_weight' => $type['max_weight'], // approximate
                        'class' => $type['class'],
                        'category' => $type['category'],
                    ]
                );
            }
        }
    }
}
