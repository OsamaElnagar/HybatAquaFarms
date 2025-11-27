<?php

namespace App\Filament\Resources\SalesOrders\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOrdersStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalOrders = SalesOrder::count();
        $pendingOrders = SalesOrder::where('payment_status', 'pending')->count();
        $paidOrders = SalesOrder::where('payment_status', 'paid')->count();

        $totalRevenue = SalesOrder::sum('net_amount');
        $thisMonthRevenue = SalesOrder::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('net_amount');

        $pendingValue = SalesOrder::whereIn('payment_status', ['pending', 'partial'])->sum('net_amount');
        $deliveredOrders = SalesOrder::where('delivery_status', 'delivered')->count();

        return [
            Stat::make('إجمالي المبيعات', number_format($totalOrders))
                ->description($paidOrders.' مدفوع، '.$pendingOrders.' معلق')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('إجمالي الإيرادات', number_format($totalRevenue).' ج.م')
                ->description('مبيعات هذا الشهر: '.number_format($thisMonthRevenue).' ج.م')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('المستحقات', number_format($pendingValue).' ج.م')
                ->description('مبيعات معلقة أو جزئية الدفع')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingValue > 0 ? 'warning' : 'success'),

            Stat::make('مبيعات تم توصيلها', number_format($deliveredOrders))
                ->description('من إجمالي '.number_format($totalOrders).' عملية')
                ->descriptionIcon('heroicon-o-truck')
                ->color('info'),
        ];
    }
}
