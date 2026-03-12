<?php

namespace App\Filament\Resources\Traders\Widgets;

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

        $totalSales = (float) $trader->salesOrders()->sum('net_amount');
        // $pendingSales = (float) $trader->salesOrders()
        //     ->whereIn('payment_status', ['pending', 'partial'])
        //     ->sum('net_amount');

        // $clearingTotal = (float) $trader->clearingEntries()->sum('amount');
        // $receiptVouchersTotal = (float) $trader->vouchers()
        //     ->where('voucher_type', \App\Enums\VoucherType::Receipt)
        //     ->sum('amount');

        $totalLoans = (float) $trader->partnerLoans()->sum('amount');
        $totalRepayments = (float) $trader->partnerLoans()
            ->join('partner_loan_repayments', 'partner_loans.id', '=', 'partner_loan_repayments.partner_loan_id')
            ->sum('partner_loan_repayments.amount');
        $loansBalance = $totalLoans - $totalRepayments;

        $outstandingReceivable = $trader->outstanding_balance;
        $netBalance = $outstandingReceivable - $loansBalance;

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSales).' EGP')
                ->description('إجمالي كل المبيعات لهذا التاجر')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('المستحقات (لنا)', number_format($outstandingReceivable).' EGP')
                ->description('مبيعات آجلة لم يتم تحصيلها بعد')
                ->color($outstandingReceivable > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('السلف (علينا)', number_format($loansBalance).' EGP')
                ->description('إجمالي السلف: '.number_format($totalLoans).' | المسدد: '.number_format($totalRepayments))
                ->color($loansBalance > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('صافي الرصيد', number_format(abs($netBalance)).' EGP')
                ->description($netBalance >= 0 ? 'لصالح المزرعة' : 'لصالح التاجر')
                ->color($netBalance >= 0 ? 'success' : 'danger')
                ->icon($netBalance >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'),
        ];
    }
}
