<?php

namespace App\Filament\Resources\Batches\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BatchPaymentSummaryWidget extends BaseWidget
{
    public ?\App\Models\Batch $record = null;

    protected function getStats(): array
    {
        $record = $this->record ?? $this->getParent()?->record ?? null;

        if (! $record instanceof \App\Models\Batch) {
            return [];
        }

        $totalCost = (float) ($record->total_cost ?? 0);
        $totalPaid = $record->total_paid;
        $outstanding = $record->outstanding_balance;
        $paymentCount = $record->batchPayments()->count();

        if ($totalCost <= 0) {
            return [
                Stat::make('التكلفة الإجمالية', 'لا يوجد تكلفة')
                    ->description('هذه الدفعة لا تحتوي على تكلفة')
                    ->color('gray'),
            ];
        }

        $paidPercentage = $totalCost > 0 ? round(($totalPaid / $totalCost) * 100, 1) : 0;

        return [
            Stat::make('التكلفة الإجمالية', number_format($totalCost).' EGP ')
                ->description('إجمالي تكلفة شراء الزريعة')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('المدفوع', number_format($totalPaid).' EGP ')
                ->description("{$paymentCount} دفعة - {$paidPercentage}% من الإجمالي")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($paidPercentage >= 100 ? 'success' : ($paidPercentage >= 80 ? 'warning' : 'info')),

            Stat::make('المتبقي', number_format($outstanding).' EGP ')
                ->description($outstanding > 0 ? 'مبلغ متبقي للدفع' : 'تم الدفع بالكامل')
                ->descriptionIcon($outstanding > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-badge')
                ->color($outstanding > 0 ? 'danger' : 'success'),
        ];
    }
}
