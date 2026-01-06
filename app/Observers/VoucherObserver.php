<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\VoucherType;
use App\Models\Voucher;

class VoucherObserver
{
    public function __construct(private PostingService $posting) {}

    public function creating(Voucher $voucher): void
    {
        if (! $voucher->voucher_number) {
            $voucher->voucher_number = static::generateVoucherNumber($voucher);
        }
    }

    public function created(Voucher $voucher): void
    {
        $event = $voucher->voucher_type === VoucherType::Payment ? 'voucher.payment' : 'voucher.receipt';

        $context = [
            'amount' => (float) $voucher->amount,
            'farm_id' => $voucher->farm_id,
            'date' => $voucher->date?->toDateString(),
            'source_type' => $voucher->getMorphClass(),
            'source_id' => $voucher->id,
            'description' => $voucher->description,
        ];

        if ($voucher->voucher_type === VoucherType::Payment) {
            // Payment: Debit Category Account, Credit Treasury Account
            $context['debit_account_id'] = $voucher->account_id;
            $context['credit_account_id'] = $voucher->treasury_account_id;
        } else {
            // Receipt: Debit Treasury Account, Credit Category Account
            $context['debit_account_id'] = $voucher->treasury_account_id;
            $context['credit_account_id'] = $voucher->account_id;
        }

        $this->posting->post($event, $context);

    }

    protected static function generateVoucherNumber(Voucher $voucher): string
    {
        // Get the last voucher for this farm and type
        $lastVoucher = Voucher::where('farm_id', $voucher->farm_id)
            ->where('voucher_type', $voucher->voucher_type)
            ->latest('id')
            ->first();

        // Extract number from last voucher (format: P-0001 or R-0001)
        $number = 1;
        if ($lastVoucher && preg_match('/-(\d+)$/', $lastVoucher->voucher_number, $matches)) {
            $number = (int) $matches[1] + 1;
        }

        // Prefix: P for Payment, R for Receipt
        $prefix = $voucher->voucher_type === VoucherType::Payment ? 'P' : 'R';

        return $prefix.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
