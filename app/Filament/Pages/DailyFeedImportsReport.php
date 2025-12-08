<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DailyFeedImportsChart;
use BackedEnum;
use Filament\Pages\Page;

class DailyFeedImportsReport extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }

    public static function getNavigationLabel(): string
    {
        return 'واردات الأعلاف اليومية';
    }

    protected static ?string $title = 'تقرير واردات الأعلاف اليومية';

    protected string $view = 'filament.pages.daily-feed-imports-report';

    protected function getHeaderWidgets(): array
    {
        return [
            DailyFeedImportsChart::class,
        ];
    }
}
