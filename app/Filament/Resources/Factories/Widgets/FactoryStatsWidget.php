<?php

namespace App\Filament\Resources\Factories\Widgets;

use App\Models\Factory;
use App\Models\JournalLine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class FactoryStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record || ! $this->record instanceof Factory) {
            return [];
        }

        $factory = $this->record;

        // Sum credits for purchases (increases what we owe to factory)
        $totalPurchases = (float) JournalLine::query()
            ->where('account_id', $factory->account_id)
            ->sum('credit');

        // Sum debits for payments (decreases what we owe to factory)
        $totalPayments = (float) JournalLine::query()
            ->where('account_id', $factory->account_id)
            ->sum('debit');

        $outstandingPayable = $factory->outstanding_balance;

        return [
            Stat::make('إجمالي المشتريات', number_format($totalPurchases).' EGP')
                ->description('إجمالي كل المشتريات المسجلة من المصنع')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make($outstandingPayable >= 0 ? 'المستحقات (للمصنع)' : 'رصيد دائن (لنا)', number_format(abs($outstandingPayable)).' EGP')
                ->description($outstandingPayable >= 0 ? 'متبقى للمصنع علينا' : 'دفعنا أكبر من المشتريات')
                ->color($outstandingPayable >= 0 ? 'danger' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('إجمالي المدفوعات', number_format($totalPayments).' EGP')
                ->description('إجمالي المبالغ المصروفة للمصنع')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('الحالة', $outstandingPayable > 0 ? 'دائن' : ($outstandingPayable < 0 ? 'مدين (له رصيد)' : 'متوازن'))
                ->description($outstandingPayable > 0 ? 'المصنع يطالبنا بسداد المبلغ' : ($outstandingPayable < 0 ? 'المصنع استلم أكثر من الفواتير' : 'لا توجد مديونية'))
                ->color($outstandingPayable > 0 ? 'danger' : ($outstandingPayable < 0 ? 'success' : 'success'))
                ->icon($outstandingPayable > 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-check-circle'),
        ];
    }
}
