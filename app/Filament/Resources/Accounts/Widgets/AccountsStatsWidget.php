<?php

namespace App\Filament\Resources\Accounts\Widgets;

use App\Enums\AccountType;
use App\Models\Account;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Account::count();
        $active = Account::where('is_active', true)->count();
        $byType = Account::query()
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type');

        return [
            Stat::make('إجمالي الحسابات', number_format($total))
                ->description($active.' نشط')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),

            Stat::make('حسابات نشطة', number_format($active))
                ->description('من إجمالي '.number_format($total))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('حسب النوع', collect(AccountType::cases())->map(fn($t) => ($byType[$t->value] ?? 0).' '.$t->name)->join(' | '))
                ->description('توزيع حسب النوع')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
