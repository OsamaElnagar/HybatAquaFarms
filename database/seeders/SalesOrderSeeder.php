<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\SalesItem;
use App\Models\SalesOrder;
use App\Models\Trader;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $traders = Trader::all();

        Farm::each(function ($farm) use ($traders) {
            $batches = Batch::where('farm_id', $farm->id)->get();

            if ($batches->isEmpty()) {
                return;
            }

            // Create 3-7 sales orders per farm
            for ($i = 0; $i < rand(3, 7); $i++) {
                $subtotal = rand(20000, 100000);

                $salesOrder = SalesOrder::create([
                    'order_number' => "{$farm->code}-SO-".str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'farm_id' => $farm->id,
                    'trader_id' => $traders->random()->id,
                    'date' => now()->subDays(rand(1, 90)),
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'discount_amount' => rand(0, $subtotal * 0.05),
                    'total_amount' => $subtotal,
                    'payment_status' => [PaymentStatus::Pending, PaymentStatus::Partial, PaymentStatus::Paid][rand(0, 2)],
                    'delivery_status' => ['delivered', 'in_transit', 'pending'][rand(0, 2)],
                ]);

                // Create 1-3 sales items per order
                for ($j = 0; $j < rand(1, 3); $j++) {
                    $batch = $batches->random();
                    $weight = rand(100, 1000);
                    $unitPrice = rand(25, 45);

                    SalesItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'batch_id' => $batch->id,
                        'species_id' => $batch->species_id,
                        'quantity' => rand(50, 500),
                        'weight' => $weight,
                        'unit_price' => $unitPrice,
                        'total_price' => $weight * $unitPrice,
                    ]);
                }
            }
        });
    }
}
