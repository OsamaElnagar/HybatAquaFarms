<?php

namespace App\Filament\Pages;

use DefStudio\Telegraph\Models\TelegraphChat;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class TelegramReports extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static \UnitEnum|string|null $navigationGroup = 'التقارير';

    protected static ?string $title = 'تقارير تيليجرام';

    protected static ?string $navigationLabel = 'تقارير تيليجرام';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.telegram-reports';

    public function sendReport(string $type)
    {
        $chats = TelegraphChat::all();

        if ($chats->isEmpty()) {
            Notification::make()
                ->title('خطأ')
                ->body('لا توجد محادثات تيليجرام مسجلة.')
                ->danger()
                ->send();

            return;
        }

        try {
            if ($type === 'daily_pdf') {
                Artisan::call('reports:daily-sales');
            } else {
                $html = '';
                switch ($type) {
                    case 'sales':
                        $html = app(\App\Services\Telegram\SalesReportService::class)->generateReport();
                        break;
                    case 'harvest':
                        $html = app(\App\Services\Telegram\HarvestReportService::class)->generateReport();
                        break;
                    case 'feedStock':
                        $html = app(\App\Services\Telegram\FeedStockReportService::class)->generateReport();
                        break;
                    case 'batches':
                        $html = app(\App\Services\Telegram\BatchReportService::class)->generateActiveBatchesReport();
                        break;
                    case 'expenses':
                        $html = app(\App\Services\Telegram\ExpenseReportService::class)->generateReport();
                        break;
                    case 'cashflow':
                        $html = app(\App\Services\Telegram\CashflowReportService::class)->generateReport();
                        break;
                    case 'advances':
                        $html = app(\App\Services\Telegram\AdvanceReportService::class)->generateReport();
                        break;
                    default:
                        throw new \Exception('نوع التقرير غير معروف.');
                }

                foreach ($chats as $chat) {
                    $chat->html($html)->send();
                }
            }

            Notification::make()
                ->title('تم إرسال التقرير بنجاح')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('حدث خطأ أثناء الإرسال')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
