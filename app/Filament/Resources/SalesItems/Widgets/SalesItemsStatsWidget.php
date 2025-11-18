<?php

namespace App\Filament\Resources\SalesItems\Widgets;

use App\Models\SalesItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesItemsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Overall stats
        $totalItems = SalesItem::count();
        $totalQuantity = (float) SalesItem::sum("quantity");
        $totalWeight = (float) SalesItem::sum("weight_kg");
        $totalValue = (float) SalesItem::sum("total_price");
        $totalDiscount = (float) SalesItem::sum("discount_amount");

        // Fulfillment stats
        $pendingItems = SalesItem::where(
            "fulfillment_status",
            "pending",
        )->count();
        $partialItems = SalesItem::where(
            "fulfillment_status",
            "partial",
        )->count();
        $fulfilledItems = SalesItem::where(
            "fulfillment_status",
            "fulfilled",
        )->count();

        $pendingValue = (float) SalesItem::where(
            "fulfillment_status",
            "pending",
        )->sum("total_price");
        $fulfilledValue = (float) SalesItem::where(
            "fulfillment_status",
            "fulfilled",
        )->sum("total_price");

        // This month stats
        $thisMonthItems = SalesItem::whereHas("salesOrder", function ($query) {
            $query
                ->whereMonth("date", Carbon::now()->month)
                ->whereYear("date", Carbon::now()->year);
        })->count();

        $thisMonthValue = (float) SalesItem::whereHas("salesOrder", function (
            $query,
        ) {
            $query
                ->whereMonth("date", Carbon::now()->month)
                ->whereYear("date", Carbon::now()->year);
        })->sum("total_price");

        // Last month stats for comparison
        $lastMonthValue = (float) SalesItem::whereHas("salesOrder", function (
            $query,
        ) {
            $query
                ->whereMonth("date", Carbon::now()->subMonth()->month)
                ->whereYear("date", Carbon::now()->subMonth()->year);
        })->sum("total_price");

        $monthlyChange =
            $lastMonthValue > 0
                ? (($thisMonthValue - $lastMonthValue) / $lastMonthValue) * 100
                : 0;

        // Average values
        $avgItemValue = $totalItems > 0 ? $totalValue / $totalItems : 0;
        $avgFishWeight =
            $totalQuantity > 0 ? ($totalWeight * 1000) / $totalQuantity : 0;

        // Premium items (Grade A)
        $premiumItems = SalesItem::where("grade", "A")->count();
        $premiumValue = (float) SalesItem::where("grade", "A")->sum(
            "total_price",
        );

        return [
            Stat::make("إجمالي أصناف البيع", number_format($totalItems))
                ->description($thisMonthItems . " صنف هذا الشهر")
                ->descriptionIcon("heroicon-o-shopping-bag")
                ->color("primary")
                ->chart([7, 4, 8, 5, 9, 6, $thisMonthItems]),

            Stat::make("إجمالي القيمة", number_format($totalValue, 2) . " ج.م")
                ->description(
                    ($monthlyChange >= 0 ? "+" : "") .
                        number_format($monthlyChange, 1) .
                        "% عن الشهر الماضي",
                )
                ->descriptionIcon(
                    $monthlyChange >= 0
                        ? "heroicon-o-arrow-trending-up"
                        : "heroicon-o-arrow-trending-down",
                )
                ->color($monthlyChange >= 0 ? "success" : "danger")
                ->chart([$lastMonthValue, $thisMonthValue]),

            Stat::make(
                "الكمية الإجمالية",
                number_format($totalQuantity, 0) . " قطعة",
            )
                ->description(number_format($totalWeight, 3) . " كجم")
                ->descriptionIcon("heroicon-o-scale")
                ->color("info"),

            Stat::make(
                "متوسط وزن السمكة",
                number_format($avgFishWeight, 2) . " جم",
            )
                ->description("عبر جميع الأصناف")
                ->descriptionIcon("heroicon-o-beaker")
                ->color("warning"),

            Stat::make(
                "متوسط قيمة الصنف",
                number_format($avgItemValue, 2) . " ج.م",
            )
                ->description("متوسط قيمة البند الواحد")
                ->descriptionIcon("heroicon-o-calculator")
                ->color("primary"),

            Stat::make(
                "حالة التنفيذ",
                number_format($fulfilledItems) . " مكتمل",
            )
                ->description(
                    $pendingItems . " معلق، " . $partialItems . " جزئي",
                )
                ->descriptionIcon("heroicon-o-check-circle")
                ->color($pendingItems > 0 ? "warning" : "success"),

            Stat::make("قيمة معلقة", number_format($pendingValue, 2) . " ج.م")
                ->description("أصناف لم تنفذ بعد")
                ->descriptionIcon("heroicon-o-clock")
                ->color($pendingValue > 0 ? "danger" : "success"),

            Stat::make(
                "إجمالي الخصومات",
                number_format($totalDiscount, 2) . " ج.م",
            )
                ->description(
                    $totalValue > 0
                        ? number_format(
                                ($totalDiscount /
                                    ($totalValue + $totalDiscount)) *
                                    100,
                                1,
                            ) . "% من الإجمالي"
                        : "0%",
                )
                ->descriptionIcon("heroicon-o-receipt-percent")
                ->color("warning"),

            Stat::make("أصناف درجة A", number_format($premiumItems))
                ->description(
                    "بقيمة " . number_format($premiumValue, 2) . " ج.م",
                )
                ->descriptionIcon("heroicon-o-star")
                ->color("success"),
        ];
    }
}
