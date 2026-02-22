<?php

namespace App\Services\Telegram;

use App\Models\Batch;

class BatchReportService
{
    public function generateActiveBatchesReport(): string
    {
        $activeBatches = Batch::with(['farm', 'species', 'dailyFeedIssues'])
            ->where('is_cycle_closed', false)
            ->get();

        if ($activeBatches->isEmpty()) {
            return '✅ <i>There are no active batches at the moment.</i>';
        }

        $html = "🐟 <b><u>ACTIVE BATCHES REPORT</u></b> 🐟\n";
        $html .= "<i>Found {$activeBatches->count()} active cycles</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($activeBatches as $batch) {
            $farmName = $batch->farm->name ?? 'Unknown Farm';
            $speciesName = $batch->species->name ?? 'Mixed/Unknown';
            $daysActive = $batch->days_since_entry;

            // Mortality logic
            $mortality = $batch->initial_quantity - $batch->current_quantity;
            $mortalityRate = $batch->initial_quantity > 0
                ? round(($mortality / $batch->initial_quantity) * 100, 2)
                : 0;

            // Financials
            $feedConsumed = number_format($batch->total_feed_consumed);
            $totalExpenses = number_format($batch->total_cycle_expenses, 2);
            $totalPaid = number_format($batch->total_paid, 2);
            $balance = number_format($batch->outstanding_balance, 2);

            $html .= "🟦 <b>Batch:</b> <code>{$batch->batch_code}</code>\n";
            $html .= "📍 <b>Farm:</b> {$farmName} | 🐟 {$speciesName}\n";
            $html .= "⏱️ <b>Time Active:</b> {$daysActive} days\n";
            $html .= '📉 <b>Mortality:</b> '.number_format($mortality)." (<code>{$mortalityRate}%</code>)\n";
            $html .= "🌾 <b>Feed Eaten:</b> <code>{$feedConsumed} kg</code>\n";
            $html .= "💸 <b>Total Cost:</b> <code>{$totalExpenses} EGP</code>\n";
            $html .= "💳 <b>Outstanding:</b> <code>{$balance} EGP</code>\n";
            $html .= "\n";
        }

        $html .= '<i>Data generated in real-time.</i>';

        return $html;
    }
}
