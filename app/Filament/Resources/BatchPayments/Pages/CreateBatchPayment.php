<?php

namespace App\Filament\Resources\BatchPayments\Pages;

use App\Filament\Resources\BatchPayments\BatchPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBatchPayment extends CreateRecord
{
    protected static string $resource = BatchPaymentResource::class;
}
