<?php

namespace App\Http\Controllers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Stringable;

class TelegramWebhookHandler extends WebhookHandler
{
    public function sales(\App\Services\Telegram\SalesReportService $service)
    {
        $this->chat->html('<i>Fetching sales data...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function report()
    {
        $this->chat->html('<i>Generating your PDF report. Please wait...</i> ⏳')->send();
        Artisan::call('reports:daily-sales');
    }

    public function harvest(\App\Services\Telegram\HarvestReportService $service)
    {
        $this->chat->html('<i>Fetching harvest data...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function feedStock(\App\Services\Telegram\FeedStockReportService $service)
    {
        $this->chat->html('<i>Fetching feed stock alerts...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function batches(\App\Services\Telegram\BatchReportService $batchReportService)
    {
        $this->chat->html('<i>Fetching active batches data...</i> ⏳')->send();
        $this->chat->html($batchReportService->generateActiveBatchesReport())->send();
    }

    public function expenses(\App\Services\Telegram\ExpenseReportService $service)
    {
        $this->chat->html('<i>Fetching expenses data...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function cashflow(\App\Services\Telegram\CashflowReportService $service)
    {
        $this->chat->html('<i>Fetching cashflow data...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function advances(\App\Services\Telegram\AdvanceReportService $service)
    {
        $this->chat->html('<i>Fetching advances data...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
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
