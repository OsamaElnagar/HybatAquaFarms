<?php

namespace App\Filament\Resources\AdvanceRepayments\Pages;

use App\Filament\Resources\AdvanceRepayments\AdvanceRepaymentResource;
use App\Filament\Resources\AdvanceRepayments\Widgets\AdvanceRepaymentsStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvanceRepayments extends ListRecords
{
    protected static string $resource = AdvanceRepaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvanceRepaymentsStatsWidget::class,
        ];
    }
}
