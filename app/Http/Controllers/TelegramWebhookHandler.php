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
        $service = app(\App\Services\Telegram\SalesReportService::class);
        $this->chat->html('<i>جاري جلب بيانات المبيعات...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function report()
    {
        $this->chat->html('<i>جاري إنشاء تقرير PDF الخاص بك. يرجى الانتظار...</i> ⏳')->send();
        Artisan::call('reports:daily-sales');
    }

    public function harvest()
    {
        $service = app(\App\Services\Telegram\HarvestReportService::class);
        $this->chat->html('<i>جاري جلب بيانات الحصاد...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function feedStock()
    {
        $service = app(\App\Services\Telegram\FeedStockReportService::class);
        $this->chat->html('<i>جاري جلب تنبيهات المخزون...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function batches()
    {
        $batchReportService = app(\App\Services\Telegram\BatchReportService::class);
        $this->chat->html('<i>جاري جلب بيانات الدورات النشطة...</i> ⏳')->send();
        $this->chat->html($batchReportService->generateActiveBatchesReport())->send();
    }

    public function expenses()
    {
        $service = app(\App\Services\Telegram\ExpenseReportService::class);
        $this->chat->html('<i>جاري جلب بيانات المصروفات...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function cashflow()
    {
        $service = app(\App\Services\Telegram\CashflowReportService::class);
        $this->chat->html('<i>جاري جلب بيانات التدفقات النقدية...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function advances()
    {
        $service = app(\App\Services\Telegram\AdvanceReportService::class);
        $this->chat->html('<i>جاري جلب بيانات السلف...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function menu()
    {
        $this->chat->html('<b>مرحباً بك في نظام إدارة المزرعة 🐟</b>'."\n\n".'يرجى تحديد التقرير الذي ترغب في عرضه من القائمة أدناه:')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('💰 المبيعات')->action('sales'),
                Button::make('🌾 الحصاد')->action('harvest'),
                Button::make('⚠️ مخزون الأعلاف')->action('feedStock'),
                Button::make('🐟 الدورات النشطة')->action('batches'),
                Button::make('💸 المصروفات')->action('expenses'),
                Button::make('🧾 الخزينة والقيود')->action('cashflow'),
                Button::make('💵 السلف')->action('advances'),
                Button::make('📄 تقرير اليوم بأكمله (PDF)')->action('report'),
            ]))->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->html("<i>Sorry, I don't understand that command. Try /menu to see available options.</i>")->send();
    }
}
