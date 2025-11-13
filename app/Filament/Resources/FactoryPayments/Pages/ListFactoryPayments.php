<?php

namespace App\Filament\Resources\FactoryPayments\Pages;

use App\Filament\Resources\FactoryPayments\FactoryPaymentResource;
use App\Filament\Resources\FactoryPayments\Widgets\FactoryPaymentsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFactoryPayments extends ListRecords
{
    protected static string $resource = FactoryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FactoryPaymentsStatsWidget::class,
        ];
    }
}
