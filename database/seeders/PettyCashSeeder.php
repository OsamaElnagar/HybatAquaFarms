<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Farm;
use App\Models\PettyCash;
use Illuminate\Database\Seeder;

class PettyCashSeeder extends Seeder
{
    public function run(): void
    {
        Farm::each(function ($farm) {
            $custodian = Employee::where('farm_id', $farm->id)->first();

            if ($custodian) {
                $pettyCashAccount = Account::where('code', '1120')->first();
                PettyCash::create([
                    'farm_id' => $farm->id,
                    'name' => "عهدة {$farm->name}",
                    'custodian_employee_id' => $custodian->id,
                    'account_id' => $pettyCashAccount?->id,
                    'opening_balance' => rand(10000, 50000),
                    'opening_date' => now()->subMonths(rand(1, 6)),
                    'is_active' => true,
                ]);
            }
        });
    }
}
