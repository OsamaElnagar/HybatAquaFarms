<?php

namespace App\Filament\Resources\BatchPayments\Pages;

use App\Filament\Resources\BatchPayments\BatchPaymentResource;
use App\Filament\Resources\BatchPayments\Widgets\BatchPaymentsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatchPayments extends ListRecords
{
    protected static string $resource = BatchPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchPaymentsStatsWidget::class,
        ];
    }
}
