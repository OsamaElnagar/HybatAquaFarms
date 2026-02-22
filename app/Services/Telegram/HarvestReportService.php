<?php

namespace App\Services\Telegram;

use App\Models\Harvest;
use Carbon\Carbon;

class HarvestReportService
{
    public function generateReport(): string
    {
        $harvests = Harvest::with(['harvestOperation.batch.farm'])
            ->whereMonth('harvest_date', Carbon::now()->month)
            ->latest('harvest_date')
            ->get();

        $count = $harvests->count();

        $html = "🌾 <b><u>HARVEST REPORT</u></b> 🌾\n";
        $html .= "<i>Total harvests this month: <b>{$count}</b></i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        if ($harvests->isEmpty()) {
            $html .= "<i>No harvests recorded this month yet.</i>\n";

            return $html;
        }

        foreach ($harvests->take(5) as $harvest) {
            $date = $harvest->harvest_date->format('Y-m-d');
            $status = $harvest->status->name ?? $harvest->status ?? 'Unknown';
            $batchCode = $harvest->harvestOperation->batch->batch_code ?? 'N/A';
            $farmName = $harvest->harvestOperation->batch->farm->name ?? 'N/A';
            $yield = number_format($harvest->harvestOperation->actual_yield ?? 0);

            $html .= "📦 <b>Batch:</b> <code>{$batchCode}</code> ({$farmName})\n";
            $html .= "     🗓️ {$date} | ⚖️ <code>{$yield} kg</code> | 🏷️ {$status}\n";
        }

        if ($count > 5) {
            $html .= "\n<i>...and ".($count - 5)." more earlier this month.</i>\n";
        }

        return $html;
    }
}
