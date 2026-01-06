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
                'created_by' => auth('web')->id(),
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

            return $salesOrder;
        });
    }
}
