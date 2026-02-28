<?php

namespace App\Filament\Resources\PettyCashTransactions\Pages;

use App\Filament\Exports\PettyCashTransactionExporter;
use App\Filament\Resources\PettyCashTransactions\PettyCashTransactionResource;
use App\Filament\Resources\PettyCashTransactions\Widgets\PettyCashTransactionsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListPettyCashTransactions extends ListRecords
{
    protected static string $resource = PettyCashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->closeModalByClickingAway(false),
            ExportAction::make()
                ->exporter(PettyCashTransactionExporter::class)
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('secondary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // PettyCashTransactionsStatsWidget::class,
        ];
    }
}
