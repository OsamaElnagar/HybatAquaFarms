<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class TreasuryDashboard extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'الخزنة';
    }

    protected static ?string $title = 'لوحة تحكم الخزنة';

    protected string $view = 'filament.pages.treasury-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\TreasuryOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\TreasuryTransactions::class,
        ];
    }
}
