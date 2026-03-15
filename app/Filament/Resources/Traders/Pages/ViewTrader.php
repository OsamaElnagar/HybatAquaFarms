<?php

namespace App\Filament\Resources\Traders\Pages;

use App\Filament\Resources\Traders\Actions\GiveCashAction;
use App\Filament\Resources\Traders\Actions\ReceivePaymentAction;
use App\Filament\Resources\Traders\Infolists\TraderInfolist;
use App\Filament\Resources\Traders\TraderResource;
use App\Filament\Resources\Traders\Widgets\TraderStatsWidget;
use App\Models\Trader;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewTrader extends ViewRecord
{
    protected static string $resource = TraderResource::class;

    public function getTitle(): string
    {
        return 'عرض: '.$this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            '#' => $this->getRecord()->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ReceivePaymentAction::make(),
            GiveCashAction::make(),
            Action::make('statementOfAccount')
                ->label('كشف الحساب')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn (Trader $record): string => TraderResource::getUrl('statement', ['record' => $record])),
            Action::make('statementsHistory')
                ->label('سجل الكشوفات')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn (Trader $record): string => TraderResource::getUrl('statements', ['record' => $record])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TraderStatsWidget::class,
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return TraderInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected static bool $isLazy = false;
}
