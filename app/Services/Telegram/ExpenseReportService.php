<?php

namespace App\Services\Telegram;

use App\Models\Voucher;
use Carbon\Carbon;

class ExpenseReportService
{
    public function generateReport(): string
    {
        $vouchers = Voucher::whereMonth('date', Carbon::now()->month)->get();

        $total = $vouchers->sum('amount');
        $count = $vouchers->count();

        $html = "💸 <b><u>VOUCHER EXPENSES</u></b> 💸\n";
        $html .= "<i>Financial outflow this month</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '💵 <b>Total Expenses:</b> <code>'.number_format((float) $total, 2)." EGP</code>\n";
        $html .= "🧾 <b>Total Vouchers:</b> {$count}\n\n";

        if ($vouchers->isNotEmpty()) {
            $html .= "📋 <b><u>Latest Vouchers:</u></b>\n";
            $latest = $vouchers->sortByDesc('date')->take(5);
            foreach ($latest as $v) {
                $date = Carbon::parse($v->date)->format('Y-m-d');
                $amt = number_format((float) $v->amount, 2);
                $desc = \Illuminate\Support\Str::limit($v->description ?? 'No description', 25);
                $html .= "🔹 <code>{$amt} EGP</code> | {$date}\n";
                $html .= "     <i>{$desc}</i>\n";
            }
        }

        return $html;
    }
}
