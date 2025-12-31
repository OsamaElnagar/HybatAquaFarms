<?php

namespace App\Actions\Sales;

use App\Models\HarvestOperation;
use App\Models\SalesOrder;
use App\Models\Trader;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreateSalesOrderFromOrders
{
    public function execute(
        HarvestOperation $harvestOperation,
        Trader $trader,
        Carbon $date,
        Collection $orders,
        ?string $notes = null
    ): SalesOrder {
        return DB::transaction(function () use ($harvestOperation, $trader, $date, $orders, $notes) {
            // 1. Create the Sales Order Header
            $salesOrder = new SalesOrder([
                'harvest_operation_id' => $harvestOperation->id,
                'trader_id' => $trader->id,
                'date' => $date,
                'created_by' => auth()->id(),
                'notes' => $notes,
            ]);

            // Manual boot logic since we want to be explicit or if we use saveQuietly
            $salesOrder->order_number = SalesOrder::generateOrderNumber();
            $salesOrder->commission_rate = $trader->commission_rate ?? 0;

            $salesOrder->save();

            // 2. Link Orders
            $salesOrder->orders()->attach($orders->pluck('id'));

            // 3. Recalculate Totals
            $salesOrder->recalculateTotals();

            // 4. Post Invoice (Accounting)
            app(\App\Domain\Accounting\PostingService::class)->post('sales.credit', [
                'amount' => (float) $salesOrder->net_amount,
                'farm_id' => $harvestOperation->farm_id,
                'date' => $salesOrder->date?->toDateString(),
                'source_type' => $salesOrder->getMorphClass(),
                'source_id' => $salesOrder->id,
                'description' => "مبيعات - أمر رقم {$salesOrder->order_number} (عملية حصاد #{$harvestOperation->operation_number})",
                'user_id' => $salesOrder->created_by,
            ]);

            return $salesOrder;
        });
    }
}
