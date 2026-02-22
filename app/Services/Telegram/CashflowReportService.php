<?php

namespace App\Services\Telegram;

use App\Models\JournalEntry;
use Carbon\Carbon;

class CashflowReportService
{
    public function generateReport(): string
    {
        $entries = JournalEntry::whereMonth('date', Carbon::now()->month)->get();

        $count = $entries->count();
        $volume = $entries->sum(function ($entry) {
            return $entry->total_debit ?? 0;
        });

        $html = "🧾 <b><u>CASHFLOW & JOURNALS</u></b> 🧾\n";
        $html .= "<i>Accounting movement this month</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>Total Entries:</b> <code>{$count}</code> operations\n";
        if ($volume > 0) {
            $html .= '🔄 <b>Est. Volume:</b> <code>'.number_format((float) $volume, 2)." EGP</code>\n\n";
        } else {
            $html .= "\n";
        }

        if ($entries->isNotEmpty()) {
            $html .= "📋 <b><u>Latest Entries:</u></b>\n";
            $latest = $entries->sortByDesc('date')->take(5);
            foreach ($latest as $e) {
                $date = Carbon::parse($e->date)->format('Y-m-d');
                $desc = \Illuminate\Support\Str::limit($e->description ?? $e->reference ?? 'Journal Entry', 30);
                $html .= "🔹 {$date} - <i>{$desc}</i>\n";
            }
        }

        return $html;
    }
}
