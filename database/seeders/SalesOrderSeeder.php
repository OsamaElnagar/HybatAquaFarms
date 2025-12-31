<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\SalesOrder;
use App\Models\Trader;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = \App\Models\Order::whereDoesntHave('salesOrders')->with('items', 'trader', 'harvestOperation.farm')->get();
        $this->command->info('Found '.$orders->count().' unbilled orders.');

        if ($orders->isEmpty()) {
            $this->command->warn('No unbilled orders found. Run OrderSeeder first.');

            return;
        }

        // Group by Trader and Harvest Operation
        $ordersGrouped = $orders->groupBy(fn ($order) => $order->trader_id.'_'.$order->harvest_operation_id);

        foreach ($ordersGrouped as $key => $groupOrders) {
            $traderId = $groupOrders->first()->trader_id;
            $operationId = $groupOrders->first()->harvest_operation_id;

            $trader = Trader::find($traderId);
            if (! $trader) {
                continue;
            }

            // Chunk group orders into sales orders
            $chunks = $groupOrders->chunk(rand(2, 5));

            foreach ($chunks as $chunkOrders) {
                $orderDate = $chunkOrders->max('date');

                $salesOrder = SalesOrder::create([
                    'order_number' => SalesOrder::generateOrderNumber(),
                    'harvest_operation_id' => $operationId,
                    'trader_id' => $traderId,
                    'date' => $orderDate,
                    'commission_rate' => $trader->commission_rate ?? 0,
                    'payment_status' => PaymentStatus::Pending,
                    'delivery_status' => DeliveryStatus::DELIVERED,
                    'created_by' => 1,
                ]);

                // Attach orders
                $salesOrder->orders()->attach($chunkOrders->pluck('id'));

                foreach ($chunkOrders as $order) {

                    // Update Item Prices if 0
                    foreach ($order->items as $item) {
                        // Determine price based on box name or classification if available
                        // Box model has full_name.
                        $boxName = $item->box->name; // assuming relation exists
                        $price = $this->getPriceForClassification($boxName);

                        $item->update([
                            'unit_price' => $price,
                            'subtotal' => $item->total_weight * $price,
                        ]);
                    }
                }

                $salesOrder->recalculateTotals();
            }
        }
    }

    private function getPriceForClassification(?string $name): float
    {
        if (! $name) {
            return 45;
        }

        // Simple mock logic
        if (str_contains($name, 'جامبو')) {
            return 70;
        }
        if (str_contains($name, '1')) {
            return 60;
        }
        if (str_contains($name, '2')) {
            return 50;
        }
        if (str_contains($name, '3')) {
            return 40;
        }

        return 45;
    }
}
