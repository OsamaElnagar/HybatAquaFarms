<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\FeedItem;
use Illuminate\Database\Seeder;

class FeedItemSeeder extends Seeder
{
    public function run(): void
    {
        $factories = Factory::whereIn('name', [
            'مصنع الحسين للأعلاف',
            'مصنع الدعاء للأعلاف',
        ])->pluck('id')->all();

        $factoryId1 = $factories[0] ?? null;
        $factoryId2 = $factories[1] ?? null;

        FeedItem::create([
            'name' => 'علف 25% بروتين',
            'description' => 'علف عالي البروتين للإصبعيات',
            'factory_id' => $factoryId1,
            'unit_of_measure' => 'kg',
            'standard_cost' => 18.50,
            'is_active' => true,
        ]);

        FeedItem::create([
            'name' => 'علف 30% بروتين',
            'description' => 'علف عالي البروتين للزريعة',
            'factory_id' => $factoryId2,
            'unit_of_measure' => 'kg',
            'standard_cost' => 22.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'name' => 'علف 20% بروتين',
            'description' => 'علف للأسماك البالغة',
            'factory_id' => $factoryId1,
            'unit_of_measure' => 'kg',
            'standard_cost' => 15.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'name' => 'علف 15% بروتين',
            'description' => 'علف اقتصادي للتسمين',
            'factory_id' => $factoryId2,
            'unit_of_measure' => 'kg',
            'standard_cost' => 12.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'name' => 'علف جمبري',
            'description' => 'علف خاص للجمبري',
            'factory_id' => $factoryId1,
            'unit_of_measure' => 'kg',
            'standard_cost' => 28.00,
            'is_active' => true,
        ]);
    }
}
