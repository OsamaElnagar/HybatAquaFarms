<?php

namespace App\Http\Controllers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Stringable;

class TelegramWebhookHandler extends WebhookHandler
{
    public function sales()
    {
        $monthly = \App\Models\SalesOrder::whereMonth('order_date', now()->month)->sum('total_after_discount');

        $this->chat->html(
            "💰 <b><u>Sales Report</u></b> 💰\n\n".
            'This Month: <code>'.number_format((float) $monthly, 2).'</code> <b>EGP</b>'
        )->send();
    }

    public function report()
    {
        $this->chat->html('<i>Generating your PDF report. Please wait...</i> ⏳')->send();
        Artisan::call('reports:daily-sales');
    }

    public function harvest()
    {
        $count = \App\Models\Harvest::whereMonth('harvest_date', now()->month)->count();
        $this->chat->html("🌾 <b><u>Harvest Report</u></b> 🌾\n\nTotal harvests recorded this month: <b>{$count}</b> operations")->send();
    }

    public function feedStock()
    {
        $lowStocks = \App\Models\FeedStock::where('current_balance', '<=', 500)
            ->with(['feedItem', 'warehouse'])
            ->take(10)
            ->get();

        if ($lowStocks->isEmpty()) {
            $this->chat->html("✅ <b><u>Feed Stock Report</u></b>\n\n<i>All feed stocks are at healthy levels!</i>")->send();

            return;
        }

        $message = "⚠️ <b><u>Low Feed Stock Alerts</u></b> ⚠️\n\n";
        foreach ($lowStocks as $stock) {
            $itemName = $stock->feedItem->name ?? 'Unknown Feed';
            $warehouseName = $stock->warehouse->name ?? 'Unknown Warehouse';
            $message .= "• {$itemName} ({$warehouseName}): <code>".number_format($stock->current_balance ?? 0)." kg</code>\n";
        }

        $this->chat->html($message)->send();
    }

    public function batches(\App\Services\Telegram\BatchReportService $batchReportService)
    {
        $this->chat->html('<i>Fetching active batches data...</i> ⏳')->send();

        $reportHtml = $batchReportService->generateActiveBatchesReport();

        $this->chat->html($reportHtml)->send();
    }

    public function expenses()
    {
        $total = \App\Models\Voucher::whereMonth('date', now()->month)->sum('amount');
        $this->chat->html("💸 <b><u>Voucher Expenses</u></b> 💸\n\nThis Month: <code>".number_format((float) $total, 2).'</code> <b>EGP</b>')->send();
    }

    public function cashflow()
    {
        $entries = \App\Models\JournalEntry::whereMonth('date', now()->month)->count();
        $this->chat->html("🧾 <b>Journal Entries (Month):</b> <code>{$entries}</code> operations recorded")->send();
    }

    public function advances()
    {
        $advances = \App\Models\EmployeeAdvance::count();
        $this->chat->html("💵 <b>Total Advances on Record:</b> <code>{$advances}</code>")->send();
    }

    public function menu()
    {
        $this->chat->html("📊 <b><u>System Reports Menu</u></b>\n\n<i>Select a report to generate:</i>")
            ->keyboard(Keyboard::make()->buttons([
                Button::make('💰 Sales')->action('sales'),
                Button::make('💸 Expenses')->action('expenses'),
                Button::make('🐟 Batches')->action('batches'),
                Button::make('🌾 Harvests')->action('harvest'),
                Button::make('⚠️ Feed Alerts')->action('feedStock'),
                Button::make('💵 Advances')->action('advances'),
                Button::make('📑 PDF Auth-Report')->action('report'),
            ]))->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->html("<i>Sorry, I don't understand that command. Try /menu to see available options.</i>")->send();
    }
}
