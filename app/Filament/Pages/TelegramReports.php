<?php

namespace App\Filament\Pages;

use DefStudio\Telegraph\Models\TelegraphChat;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
                        $data = app(\App\Services\Telegram\FeedStockReportService::class)->generateSummaryReport();
                        $html = $data['html'] ?? '';
                        break;
                    case 'batches':
                        $data = app(\App\Services\Telegram\BatchReportService::class)->generateActiveBatchesReport();
                        $html = $data['html'] ?? '';
                        break;
                    case 'expenses':
                        $html = app(\App\Services\Telegram\ExpenseReportService::class)->generateReport();
                        break;
                    case 'cashflow':
                        $html = app(\App\Services\Telegram\CashflowReportService::class)->generateReport();
                        break;
                    case 'advances':
                        $data = app(\App\Services\Telegram\AdvanceReportService::class)->generateSummaryReport();
                        $html = $data['html'] ?? '';
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
            Log::error($e->getMessage());
            Notification::make()
                ->title('حدث خطأ أثناء الإرسال')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function telegramReportsSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'md' => 2, 'lg' => 3, 'xl' => 4])
                    ->schema([
                        Callout::make('تقرير اليوم بأكمله (PDF)')
                            ->description('ملخص شامل لكل العمليات في المزرعة.')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->actions([
                                Action::make('send_daily_pdf')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->action(fn () => $this->sendReport('daily_pdf')),
                            ]),

                        Callout::make('المبيعات')
                            ->description('ملخص مبيعات هذا الشهر.')
                            ->icon('heroicon-o-banknotes')
                            ->color('success')
                            ->actions([
                                Action::make('send_sales')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('success')
                                    ->action(fn () => $this->sendReport('sales')),
                            ]),

                        Callout::make('الحصاد')
                            ->description('إجمالي عمليات الحصاد لهذا الشهر.')
                            ->icon('heroicon-o-scale')
                            ->color('warning')
                            ->actions([
                                Action::make('send_harvest')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('warning')
                                    ->action(fn () => $this->sendReport('harvest')),
                            ]),

                        Callout::make('تنبيهات مخزون الأعلاف')
                            ->description('نواقص الأعلاف بالمستودعات.')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->color('danger')
                            ->actions([
                                Action::make('send_feedStock')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('danger')
                                    ->action(fn () => $this->sendReport('feedStock')),
                            ]),

                        Callout::make('الدورات النشطة')
                            ->description('بيانات الدورات ومعدل التحويل.')
                            ->icon('heroicon-o-arrow-path-rounded-square')
                            ->color('info')
                            ->actions([
                                Action::make('send_batches')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('info')
                                    ->action(fn () => $this->sendReport('batches')),
                            ]),

                        Callout::make('المصروفات')
                            ->description('منصرفات السندات لهذا الشهر.')
                            ->icon('heroicon-o-currency-dollar')
                            ->color('danger')
                            ->actions([
                                Action::make('send_expenses')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('danger')
                                    ->action(fn () => $this->sendReport('expenses')),
                            ]),

                        Callout::make('الخزينة والقيود')
                            ->description('حركة الأموال والقيود اليومية.')
                            ->icon('heroicon-o-arrows-right-left')
                            ->color('success')
                            ->actions([
                                Action::make('send_cashflow')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('success')
                                    ->action(fn () => $this->sendReport('cashflow')),
                            ]),

                        Callout::make('السلف')
                            ->description('أرصدة سلف الموظفين المتبقية.')
                            ->icon('heroicon-o-users')
                            ->color('primary')
                            ->actions([
                                Action::make('send_advances')
                                    ->label('إرسال لـ Telegram')
                                    ->button()
                                    ->color('primary')
                                    ->action(fn () => $this->sendReport('advances')),
                            ]),
                    ]),
            ]);
    }
}
