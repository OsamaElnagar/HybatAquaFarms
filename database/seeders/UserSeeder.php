<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create owner
        User::factory()->create([
            'name' => 'المالك الرئيسي',
            'email' => 'owner@hybatfarms.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Owner,
        ]);

        User::factory()->create([
            'name' => 'معاذ النجار',
            'email' => 'mouaz@hybataquafarm.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Owner,
        ]);
        
        // Create accountant
        User::factory()->create([
            'name' => 'المحاسب الرئيسي',
            'email' => 'accountant@hybatfarms.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Accountant,
        ]);

        // Create farm managers
        User::factory()->count(3)->create([
            'user_type' => UserType::FarmManager,
            'password' => Hash::make('password'),
        ]);

        // Create workers
        User::factory()->count(5)->create([
            'user_type' => UserType::Worker,
            'password' => Hash::make('password'),
        ]);
    }
}
