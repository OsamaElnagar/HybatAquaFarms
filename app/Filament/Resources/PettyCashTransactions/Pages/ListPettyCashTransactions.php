<?php

namespace App\Filament\Resources\PettyCashTransactions\Pages;

use App\Filament\Resources\PettyCashTransactions\PettyCashTransactionResource;
use App\Filament\Resources\PettyCashTransactions\Widgets\PettyCashTransactionsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPettyCashTransactions extends ListRecords
{
    protected static string $resource = PettyCashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PettyCashTransactionsStatsWidget::class,
        ];
    }
}
