<?php

namespace Database\Seeders;

use App\Models\FeedItem;
use Illuminate\Database\Seeder;

class FeedItemSeeder extends Seeder
{
    public function run(): void
    {
        FeedItem::create([
            'code' => 'F-25',
            'name' => 'علف 25% بروتين',
            'description' => 'علف عالي البروتين للإصبعيات',
            'unit_of_measure' => 'kg',
            'standard_cost' => 18.50,
            'is_active' => true,
        ]);

        FeedItem::create([
            'code' => 'F-30',
            'name' => 'علف 30% بروتين',
            'description' => 'علف عالي البروتين للزريعة',
            'unit_of_measure' => 'kg',
            'standard_cost' => 22.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'code' => 'F-20',
            'name' => 'علف 20% بروتين',
            'description' => 'علف للأسماك البالغة',
            'unit_of_measure' => 'kg',
            'standard_cost' => 15.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'code' => 'F-15',
            'name' => 'علف 15% بروتين',
            'description' => 'علف اقتصادي للتسمين',
            'unit_of_measure' => 'kg',
            'standard_cost' => 12.00,
            'is_active' => true,
        ]);

        FeedItem::create([
            'code' => 'F-SHRIMP',
            'name' => 'علف جمبري',
            'description' => 'علف خاص للجمبري',
            'unit_of_measure' => 'kg',
            'standard_cost' => 28.00,
            'is_active' => true,
        ]);
    }
}
