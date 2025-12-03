<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class TreasuryDashboard extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'الخزينة';
    }

    protected static ?string $title = 'لوحة تحكم الخزينة';

    protected string $view = 'filament.pages.treasury-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\TreasuryOverview::class,
        ];
    }
}
