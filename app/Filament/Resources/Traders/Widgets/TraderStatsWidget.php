<?php

namespace App\Filament\Resources\Traders\Widgets;

use App\Models\JournalLine;
use App\Models\Trader;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class TraderStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record || ! $this->record instanceof Trader) {
            return [];
        }

        $trader = $this->record;

        // Sum debits for sales (increases trader's debt to us)
        $totalSales = (float) JournalLine::query()
            ->where('account_id', $trader->account_id)
            ->sum('debit');

        // Sum credits for receipts (decreases trader's debt to us)
        $totalReceipts = (float) JournalLine::query()
            ->where('account_id', $trader->account_id)
            ->sum('credit');

        $outstandingReceivable = $trader->outstanding_balance;

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSales).' EGP')
                ->description('إجمالي كل المبيعات المسجلة في الحساب')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('إجمالي التحصيلات', number_format($totalReceipts).' EGP')
                ->description('إجمالي المبالغ المدفوعة من التاجر')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make($outstandingReceivable >= 0 ? 'المستحقات (لنا)' : 'الرصيد الدائن (علينا)', number_format(abs($outstandingReceivable)).' EGP')
                ->description($outstandingReceivable >= 0 ? 'متبقى على التاجر' : 'رصيد مستحق للتاجر')
                ->color($outstandingReceivable >= 0 ? 'warning' : 'danger')
                ->icon('heroicon-o-clock'),

            Stat::make('الحالة', $outstandingReceivable > 0 ? 'مدين' : ($outstandingReceivable < 0 ? 'دائن (له رصيد)' : 'متوازن'))
                ->description($outstandingReceivable > 0 ? 'التاجر مطالب بسداد المبلغ' : ($outstandingReceivable < 0 ? 'التاجر دفع أكثر من قيمة الفواتير' : 'لا يوجد مديونية'))
                ->color($outstandingReceivable > 0 ? 'warning' : ($outstandingReceivable < 0 ? 'danger' : 'success'))
                ->icon($outstandingReceivable > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-check-circle'),
        ];
    }
}
