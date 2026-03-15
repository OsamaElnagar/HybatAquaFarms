<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\SalesOrder;

class SalesOrderObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(SalesOrder $salesOrder): void
    {
        $this->postSalesOrder($salesOrder);
    }

    public function updated(SalesOrder $salesOrder): void {}

    protected function postSalesOrder(SalesOrder $salesOrder): void
    {
        $farmId = $salesOrder->farm_id ?? $salesOrder->harvestOperation?->farm_id;
        $trader = $salesOrder->trader;

        $this->posting->post('sales.credit', [
            'amount' => (float) $salesOrder->net_amount,
            'farm_id' => $farmId,
            'date' => $salesOrder->date?->toDateString(),
            'source_type' => $salesOrder->getMorphClass(),
            'source_id' => $salesOrder->id,
            'description' => "مبيعات آجلة - أمر رقم {$salesOrder->order_number}",
            'user_id' => $salesOrder->created_by,
            'debit_account_id' => $trader?->account_id,
            'trader_statement_id' => $trader?->activeStatement?->id,
        ]);
    }
}
