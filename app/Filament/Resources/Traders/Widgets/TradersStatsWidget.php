<?php

namespace App\Filament\Resources\Traders\Widgets;

use App\Models\SalesOrder;
use App\Models\Trader;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TradersStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalTraders = Trader::count();
        $activeTraders = Trader::where('is_active', true)->count();
        $inactiveTraders = Trader::where('is_active', false)->count();

        $totalReceivables = Trader::get()->sum(fn ($trader) => $trader->outstanding_balance);
        $totalRevenue = SalesOrder::sum('net_amount');
        $pendingOrders = SalesOrder::whereIn('payment_status', ['pending', 'partial'])->count();

        return [
            Stat::make('إجمالي التجار', number_format($totalTraders))
                ->description($activeTraders.' نشط، '.$inactiveTraders.' غير نشط')
                ->descriptionIcon('heroicon-o-user-circle')
                ->color('primary'),

            Stat::make('المبيعات الإجمالية', number_format($totalRevenue).' EGP ')
                ->description('إجمالي قيمة أوامر البيع')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('success'),

            Stat::make('المستحقات', number_format($totalReceivables).' EGP ')
                ->description($pendingOrders.' أمر بيع معلق')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($totalReceivables > 0 ? 'warning' : 'success'),

            Stat::make('متوسط قيمة الطلب', $totalTraders > 0 ? number_format($totalRevenue / max(1, SalesOrder::count())).' EGP ' : '0.00 EGP')
                ->description('متوسط قيمة أمر البيع')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
