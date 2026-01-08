<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HarvestAndSalesStats;
use App\Filament\Widgets\TreasuryOverview;
use App\Filament\Widgets\TreasuryTransactions;
use BackedEnum;
use Filament\Pages\Page;

class TreasuryDashboard extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'الرئيسية';
    }

    protected static ?string $title = 'الخزنة و البيانات المهمة';

    protected string $view = 'filament.pages.treasury-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            TreasuryOverview::class,
            HarvestAndSalesStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TreasuryTransactions::class,
        ];
    }
}
