<?php

namespace App\Filament\Resources\Farms\Pages;

use App\Filament\Resources\Farms\Actions\FarmStatsAction;
use App\Filament\Resources\Farms\FarmResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFarm extends EditRecord
{
    protected static string $resource = FarmResource::class;

    public function getTitle(): string
    {
        return 'تعديل مزرعة: '.$this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            '#' => 'تعديل',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('detailsReport')
                ->label('تقرير التفاصيل')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => FarmResource::getUrl('details-report', ['record' => $this->getRecord()])),
            // FarmStatsAction::make(),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
