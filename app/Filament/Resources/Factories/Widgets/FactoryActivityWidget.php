<?php

namespace App\Filament\Resources\Factories\Widgets;

use App\Enums\FactoryType;
use App\Models\Factory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class FactoryActivityWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record || !$this->record instanceof Factory || $this->record->type === FactoryType::SUPPLIER) {
            return [];
        }

        $currentActivity = $this->record->current_year_activity;
        $pastActivity = $this->record->past_year_activity;

        $stats = [
            Stat::make('مشتريات العام الحالي', number_format($currentActivity['purchases']) . ' EGP')
                ->description('إجمالي مشتريات المصنع هذا العام')
                ->color('danger')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('مدفوعات العام الحالي', number_format($currentActivity['payments']) . ' EGP')
                ->description('إجمالي المدفوعات للمصنع هذا العام')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('مشتريات العام الماضي', number_format($pastActivity['purchases']) . ' EGP')
                ->description('إجمالي مشتريات المصنع العام الماضي')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('مدفوعات العام الماضي', number_format($pastActivity['payments']) . ' EGP')
                ->description('إجمالي المدفوعات للمصنع العام الماضي')
                ->icon('heroicon-o-banknotes'),
        ];

        return $stats;
    }
}
