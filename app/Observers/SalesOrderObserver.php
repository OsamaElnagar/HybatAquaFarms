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
        // Double Accounting Fix:
        // We do NOT post 'sales.payment' here anymore because payments should be handled
        // via Vouchers. If we post here, and also create a Voucher (which posts voucher.receipt),
        // we duplicate the cash entry.
        // The SalesOrder status change to 'Paid' is now just an informational state change.

        /*
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
        */
    }

    protected function postSalesOrder(SalesOrder $salesOrder): void
    {
        // If it's a cash sale (which we are forcing now), we DON'T post a 'sales.cash' entry here
        // because the Voucher created in CreateSalesOrder page will handle the GL entry:
        // Debit Cash / Credit Sales

        // However, if for some reason no voucher was created (legacy or bulk import),
        // we might still need this. But given the user's request to "Treat Sales Order as Receipt",
        // we should rely on the Voucher.

        // BUT, the system design (HarvestBox -> SalesOrder) implies SalesOrder is the "Invoice".
        // If we disable posting here, we lose the "Sales" record if the Voucher isn't created.

        // STRATEGY:
        // 1. If it is Paid (Cash Sale), we assume the Voucher handles the Cash <-> Sales entry.
        //    So we do NOTHING here to avoid duplication.
        // 2. If it is Credit (unlikely now), we post sales.credit.

        if ($salesOrder->payment_status === PaymentStatus::Paid) {
            return;
        }

        $farmId = $salesOrder->farm_id ?? $salesOrder->harvestOperation?->farm_id;

        $this->posting->post('sales.credit', [
            'amount' => (float) $salesOrder->net_amount,
            'farm_id' => $farmId,
            'date' => $salesOrder->date?->toDateString(),
            'source_type' => $salesOrder->getMorphClass(),
            'source_id' => $salesOrder->id,
            'description' => "مبيعات آجلة - أمر رقم {$salesOrder->order_number}",
            'user_id' => $salesOrder->created_by,
        ]);
    }
}
