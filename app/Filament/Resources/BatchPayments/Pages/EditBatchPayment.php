<?php

namespace App\Filament\Resources\BatchPayments\Pages;

use App\Filament\Resources\BatchPayments\BatchPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBatchPayment extends EditRecord
{
    protected static string $resource = BatchPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
