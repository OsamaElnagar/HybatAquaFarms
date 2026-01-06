<?php

namespace App\Filament\Resources\PostingRules\Widgets;

use App\Models\PostingRule;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostingRulesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = PostingRule::count();
        $active = PostingRule::where('is_active', true)->count();

        return [
            Stat::make('قواعد القيود', number_format($total))
                ->description($active.' نشطة')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary'),
        ];
    }
}
