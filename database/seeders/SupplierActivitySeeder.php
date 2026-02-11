<?php

namespace Database\Seeders;

use App\Models\SupplierActivity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            'تبن',
            'قش',
            'سابله',
            'مواشي',
            'أدوية',
        ];

        foreach ($activities as $activity) {
            SupplierActivity::firstOrCreate(['name' => $activity]);
        }
    }
}
