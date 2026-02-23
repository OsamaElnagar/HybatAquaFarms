<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\Batches\Widgets\BatchPaymentSummaryWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBatch extends EditRecord
{
    protected static string $resource = BatchResource::class;

    public function getTitle(): string
    {
        return 'تعديل دورة: '.$this->getRecord()->batch_code;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->batch_code,
            '#' => 'تعديل',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close_batch')
                ->label('إقفال الدورة')
                ->color('danger')
                ->icon('heroicon-o-lock-closed')
                ->url(fn () => BatchResource::getUrl('close', ['record' => $this->getRecord()]))
                ->visible(fn () => ! $this->getRecord()->is_cycle_closed),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchPaymentSummaryWidget::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
