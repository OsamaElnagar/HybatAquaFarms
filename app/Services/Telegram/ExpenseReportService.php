<?php

namespace App\Services\Telegram;

use App\Models\Voucher;
use Carbon\Carbon;

class ExpenseReportService
{
    public function generateReport(): string
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Outgoing Vouchers (Payment)
        $outVouchers = Voucher::where('voucher_type', \App\Enums\VoucherType::Payment)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        // 2. Outgoing Petty Cash
        $outPettyCash = \App\Models\PettyCashTransaction::where('direction', \App\Enums\PettyTransacionType::OUT)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalVouchers = $outVouchers->sum('amount');
        $totalPettyCash = $outPettyCash->sum('amount');

        $grandTotal = $totalVouchers + $totalPettyCash;
        $totalTransactionsCount = $outVouchers->count() + $outPettyCash->count();

        $html = "<b><u>مصروفات الشهر الحالي</u></b>\n\n";

        $html .= 'إجمالي السندات: <b>'.number_format((float) $totalVouchers)." ج.م</b>\n";
        $html .= 'إجمالي العهد: <b>'.number_format((float) $totalPettyCash)." ج.م</b>\n\n";

        $html .= '<b><u>الإجمالي العام</u>: '.number_format((float) $grandTotal)." ج.م</b>\n";
        $html .= "عدد الحركات: {$totalTransactionsCount}\n\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n\n";

        if ($totalTransactionsCount > 0) {
            $html .= "<b><u>(معاملة 25) - أحدث حركات الصرف:</u></b>\n\n";

            // Combine and sort to get latest
            $combined = collect();

            foreach ($outVouchers as $v) {
                $combined->push([
                    'date' => Carbon::parse($v->date),
                    'amount' => $v->amount,
                    'desc' => $v->description,
                    'type' => 'سند',
                ]);
            }

            foreach ($outPettyCash as $p) {
                $combined->push([
                    'date' => Carbon::parse($p->date),
                    'amount' => $p->amount,
                    'desc' => $p->description,
                    'type' => 'عهدة',
                ]);
            }

            $latest = $combined->sortByDesc('date')->take(25);

            foreach ($latest as $transaction) {
                $dateStr = $transaction['date']->format('Y-m-d');
                $amt = number_format((float) $transaction['amount']);
                $desc = \Illuminate\Support\Str::limit($transaction['desc'], 40);

                $html .= "<b>[{$transaction['type']}]</b> <code>{$amt} ج.م</code> | {$dateStr}\n";
                $html .= "<i>{$desc}</i>\n\n";
            }
        }

        return $html;
    }
}
