<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use App\Models\Employee;
use App\Models\Farm;
use App\Models\Trader;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        Farm::each(function ($farm) {
            $employees = Employee::where('farm_id', $farm->id)->get();
            $traders = Trader::take(3)->get();

            // Create 5-10 payment vouchers
            for ($i = 0; $i < rand(5, 10); $i++) {
                $counterparty = rand(1, 2) === 1 && $employees->count() > 0
                    ? $employees->random()
                    : $traders->random();

                Voucher::create([
                    'farm_id' => $farm->id,
                    'voucher_type' => VoucherType::Payment,
                    'voucher_number' => str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'date' => now()->subDays(rand(1, 90)),
                    'counterparty_type' => get_class($counterparty),
                    'counterparty_id' => $counterparty->id,
                    'amount' => rand(500, 5000),
                    'description' => ['سلفة', 'مصروف تشغيل', 'صيانة', 'نقل'][rand(0, 3)],
                    'payment_method' => PaymentMethod::CASH->value,
                ]);
            }

            // Create 3-6 receipt vouchers
            for ($i = 0; $i < rand(3, 6); $i++) {
                Voucher::create([
                    'farm_id' => $farm->id,
                    'voucher_type' => VoucherType::Receipt,
                    'voucher_number' => str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'date' => now()->subDays(rand(1, 90)),
                    'counterparty_type' => Trader::class,
                    'counterparty_id' => $traders->random()->id,
                    'amount' => rand(10000, 50000),
                    'description' => 'سداد مبيعات',
                    'payment_method' => PaymentMethod::CASH->value,
                ]);
            }
        });
    }
}
