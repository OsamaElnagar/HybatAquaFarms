<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\PaymentStatus;
use App\Models\SalesOrder;

class SalesOrderObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(SalesOrder $salesOrder): void
    {
        $this->postSalesOrder($salesOrder);
    }

    public function updated(SalesOrder $salesOrder): void
    {
        // Only handle payment status changes
        if (! $salesOrder->wasChanged('payment_status')) {
            return;
        }

        // If payment status changed from non-paid to paid, post the receipt
        if ($salesOrder->payment_status === PaymentStatus::Paid) {
            $originalStatus = $salesOrder->getOriginal('payment_status');
            $previousStatus = $originalStatus instanceof PaymentStatus
                ? $originalStatus
                : PaymentStatus::from($originalStatus);

            // Only post receipt if it was previously pending or partial
            if ($previousStatus === PaymentStatus::Pending || $previousStatus === PaymentStatus::Partial) {
                // Post the payment receipt (cash received, clearing the receivable)
                // This assumes the original sale was posted as credit (debit receivables)
                // Now we're receiving cash (debit cash, credit receivables)
                $this->posting->post('sales.payment', [
                    'amount' => (float) $salesOrder->net_amount,
                    'farm_id' => $salesOrder->farm_id,
                    'date' => $salesOrder->date?->toDateString(),
                    'source_type' => $salesOrder->getMorphClass(),
                    'source_id' => $salesOrder->id,
                    'description' => "تحصيل من أمر بيع رقم {$salesOrder->order_number}",
                    'user_id' => $salesOrder->created_by,
                ]);
            }
        }
    }

    protected function postSalesOrder(SalesOrder $salesOrder): void
    {
        // Determine event key based on payment status
        $eventKey = $salesOrder->payment_status === PaymentStatus::Paid
            ? 'sales.cash'
            : 'sales.credit';

        $this->posting->post($eventKey, [
            'amount' => (float) $salesOrder->net_amount,
            'farm_id' => $salesOrder->farm_id,
            'date' => $salesOrder->date?->toDateString(),
            'source_type' => $salesOrder->getMorphClass(),
            'source_id' => $salesOrder->id,
            'description' => "مبيعات - أمر رقم {$salesOrder->order_number}",
            'user_id' => $salesOrder->created_by,
        ]);
    }
}
