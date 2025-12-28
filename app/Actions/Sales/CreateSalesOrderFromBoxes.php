<?php

namespace App\Actions\Sales;

use App\Enums\PricingUnit;
use App\Models\Farm;
use App\Models\SalesOrder;
use App\Models\Trader;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreateSalesOrderFromBoxes
{
    public function execute(
        Farm $farm,
        Trader $trader,
        Carbon $date,
        Collection $boxes,
        ?PricingUnit $pricingUnit = null,
        ?float $unitPrice = null,
        ?string $notes = null
    ): SalesOrder {
        return DB::transaction(function () use ($farm, $trader, $date, $boxes, $pricingUnit, $unitPrice, $notes) {
            // 1. Create the Sales Order Header WITHOUT triggering observer
            // We do this to avoid posting a Journal Entry with 0 amount before items are added.
            $salesOrder = new SalesOrder([
                'farm_id' => $farm->id,
                'trader_id' => $trader->id,
                'date' => $date,
                'created_by' => auth()->id(),
                'notes' => $notes,
            ]);

            // Manual boot logic since saveQuietly skips events
            $salesOrder->order_number = SalesOrder::generateOrderNumber();
            if (!$salesOrder->commission_rate) {
                $salesOrder->commission_rate = $trader->commission_rate ?? 0;
            }

            $salesOrder->saveQuietly();

            // 2. Link Boxes (Lines)
            foreach ($boxes as $box) {
                $updateData = [
                    'sales_order_id' => $salesOrder->id,
                    'trader_id' => $trader->id,
                    'is_sold' => true,
                    'sold_at' => $date,
                ];

                // Apply bulk pricing if provided
                if ($pricingUnit) {
                    $updateData['pricing_unit'] = $pricingUnit;
                }

                if ($unitPrice !== null) {
                    $updateData['unit_price'] = $unitPrice;
                }

                $box->update($updateData);

                // Trigger calculation if price was updated manually
                if ($unitPrice !== null) {
                    $box->calculateSubtotal();
                    $box->saveQuietly();
                }
            }

            // 3. Recalculate Totals
            $salesOrder->recalculateTotals();

            // 4. Post Invoice (Accounting)
            // We do this manually here because we suppressed the created observer
            // This ensures we post the correct final amount
            app(\App\Domain\Accounting\PostingService::class)->post('sales.credit', [
                'amount' => (float) $salesOrder->net_amount,
                'farm_id' => $salesOrder->farm_id,
                'date' => $salesOrder->date?->toDateString(),
                'source_type' => $salesOrder->getMorphClass(),
                'source_id' => $salesOrder->id,
                'description' => "مبيعات - أمر رقم {$salesOrder->order_number}",
                'user_id' => $salesOrder->created_by,
            ]);

            return $salesOrder;
        });
    }
}
