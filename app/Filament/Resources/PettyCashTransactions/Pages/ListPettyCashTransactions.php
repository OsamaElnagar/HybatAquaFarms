<?php

namespace App\Filament\Resources\PettyCashTransactions\Pages;

use App\Enums\PettyTransacionType;
use App\Filament\Exports\PettyCashTransactionExporter;
use App\Filament\Resources\PettyCashTransactions\PettyCashTransactionResource;
use App\Filament\Resources\PettyCashTransactions\Widgets\PettyCashTransactionsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPettyCashTransactions extends ListRecords
{
    protected static string $resource = PettyCashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->closeModalByClickingAway(false),
            ExportAction::make()
                ->exporter(PettyCashTransactionExporter::class),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // PettyCashTransactionsStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'الكل' => Tab::make(),
            'المقبوضات' => Tab::make()->modifyQueryUsing(fn ($query) => $query->where('direction', PettyTransacionType::IN)),
            'المدفوعات' => Tab::make()->modifyQueryUsing(fn ($query) => $query->where('direction', PettyTransacionType::OUT)),
        ];
    }
}
