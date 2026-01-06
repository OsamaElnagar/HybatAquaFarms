<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Enums\PaymentStatus;
use App\Enums\VoucherType;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\Account;
use App\Models\Voucher;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['payment_status'] = PaymentStatus::Paid;

        $salesOrder = static::getModel()::create($data);

        $salesOrder->loadMissing('harvestOperation');

        $farmId = $salesOrder->harvestOperation?->farm_id;

        if ($farmId) {
            $treasuryAccount = Account::where('code', '1120')
                ->where('farm_id', $farmId)
                ->first();

            $salesAccount = Account::where('code', '4100')
                ->where('farm_id', $farmId)
                ->first();

            Voucher::create([
                'farm_id' => $farmId,
                'voucher_type' => VoucherType::Receipt,
                'date' => $salesOrder->date,
                'counterparty_type' => 'App\Models\Trader',
                'counterparty_id' => $salesOrder->trader_id,
                'treasury_account_id' => $treasuryAccount?->id,
                'account_id' => $salesAccount?->id,
                'amount' => $salesOrder->net_amount,
                'description' => "تحصيل فوري - أمر بيع رقم {$salesOrder->order_number}",
                'created_by' => auth('web')->id(),
            ]);
        }

        return $salesOrder;
    }
}
