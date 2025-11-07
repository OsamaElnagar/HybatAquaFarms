<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\VoucherType;
use App\Models\PettyCashTransaction;
use App\Models\Voucher;

class VoucherObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(Voucher $voucher): void
    {
        $event = $voucher->voucher_type === VoucherType::Payment ? 'voucher.payment' : 'voucher.receipt';

        $this->posting->post($event, [
            'amount' => (float) $voucher->amount,
            'farm_id' => $voucher->farm_id,
            'date' => $voucher->date?->toDateString(),
            'source_type' => $voucher->getMorphClass(),
            'source_id' => $voucher->id,
            'description' => $voucher->description,
        ]);

        // If voucher is linked to petty cash, create transaction automatically
        if ($voucher->petty_cash_id) {
            PettyCashTransaction::create([
                'petty_cash_id' => $voucher->petty_cash_id,
                'voucher_id' => $voucher->id,
                'date' => $voucher->date ?? now(),
                'direction' => $voucher->voucher_type === VoucherType::Payment ? 'out' : 'in',
                'amount' => $voucher->amount,
                'description' => $voucher->description ?? 'معاملة من سند',
                'recorded_by' => $voucher->created_by,
            ]);
        }
    }
}
