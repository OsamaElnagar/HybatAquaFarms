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
        User::factory()->withoutTwoFactor()->create([
            'name' => 'المالك',
            'email' => 'owner@hybataquafarm.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Owner,
        ]);

        User::factory()->withoutTwoFactor()->create([
            'name' => 'معاذ النجار',
            'email' => 'mouaz@hybataquafarm.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Owner,
        ]);

        // Create accountant
        User::factory()->withoutTwoFactor()->create([
            'name' => 'المحاسب',
            'email' => 'accountant@hybataquafarm.com',
            'password' => Hash::make('password'),
            'user_type' => UserType::Accountant,
        ]);
    }
}
