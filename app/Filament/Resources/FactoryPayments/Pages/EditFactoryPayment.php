<?php

namespace App\Filament\Resources\FactoryPayments\Pages;

use App\Filament\Resources\FactoryPayments\FactoryPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFactoryPayment extends EditRecord
{
    protected static string $resource = FactoryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
