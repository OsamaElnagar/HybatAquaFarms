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

        $data = $service->generateSummaryReport();

        $keyboard = Keyboard::make();
        foreach ($data['warehouses'] as $warehouse) {
            $name = $warehouse->name;
            if ($warehouse->farm) {
                $name .= ' ('.$warehouse->farm->name.')';
            }
            $keyboard->button($name)->action('warehouseStock')->param('id', $warehouse->id);
        }

        $this->chat->html($data['html'])->keyboard($keyboard)->send();
    }

    public function warehouseStock(int $id)
    {
        $service = app(\App\Services\Telegram\FeedStockReportService::class);
        $html = $service->generateWarehouseReport($id);

        // Send as a new HTML message to the chat
        $this->chat->html($html)->send();
    }

    public function batches()
    {
        $batchReportService = app(\App\Services\Telegram\BatchReportService::class);
        $this->chat->html('<i>جاري جلب بيانات الدورات النشطة...</i> ⏳')->send();

        $data = $batchReportService->generateActiveBatchesReport();

        $keyboard = Keyboard::make();
        if (isset($data['batches']) && $data['batches']->isNotEmpty()) {
            foreach ($data['batches'] as $batch) {
                $name = $batch->batch_code;
                if ($batch->farm) {
                    $name .= ' ('.$batch->farm->name.')';
                }
                $keyboard->button($name)->action('batchReport')->param('id', $batch->id);
            }
        }

        if ($keyboard->isEmpty()) {
            $this->chat->html($data['html'] ?? $data)->send();
        } else {
            $this->chat->html($data['html'])->keyboard($keyboard)->send();
        }
    }

    public function batchReport(int $id)
    {
        $service = app(\App\Services\Telegram\BatchReportService::class);
        $html = $service->generateBatchReport($id);

        $this->chat->html($html)->send();
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

        $data = $service->generateSummaryReport();

        $keyboard = Keyboard::make();
        if (isset($data['employees']) && $data['employees']->isNotEmpty()) {
            foreach ($data['employees'] as $employee) {
                $name = $employee->name;
                if ($employee->farm) {
                    $name .= ' ('.$employee->farm->name.')';
                }
                $keyboard->button($name)->action('advanceReport')->param('id', $employee->id);
            }
        }

        if ($keyboard->isEmpty()) {
            $this->chat->html($data['html'] ?? $data)->send();
        } else {
            $this->chat->html($data['html'])->keyboard($keyboard)->send();
        }
    }

    public function advanceReport(int $id)
    {
        $service = app(\App\Services\Telegram\AdvanceReportService::class);
        $html = $service->generateEmployeeAdvanceReport($id);

        $this->chat->html($html)->send();
    }

    public function dailyFeedIssues()
    {
        $service = app(\App\Services\Telegram\DailyFeedIssueReportService::class);
        $this->chat->html('<i>جاري جلب بيانات منصرف الأعلاف...</i> ⏳')->send();
        $this->chat->html($service->generateReport())->send();
    }

    public function employees()
    {
        $service = app(\App\Services\Telegram\EmployeeReportService::class);
        $this->chat->html('<i>جاري جلب بيانات الموظفين...</i> ⏳')->send();

        $data = $service->generateSummaryReport();

        $keyboard = Keyboard::make();
        if (isset($data['employees']) && $data['employees']->isNotEmpty()) {
            foreach ($data['employees'] as $employee) {
                $name = $employee->name;
                if ($employee->farm) {
                    $name .= ' ('.$employee->farm->name.')';
                }
                $keyboard->button($name)->action('employeeReport')->param('id', $employee->id);
            }
        }

        if ($keyboard->isEmpty()) {
            $this->chat->html($data['html'] ?? $data)->send();
        } else {
            $this->chat->html($data['html'])->keyboard($keyboard)->send();
        }
    }

    public function employeeReport(int $id)
    {
        $service = app(\App\Services\Telegram\EmployeeReportService::class);
        $html = $service->generateEmployeeReport($id);

        $this->chat->html($html)->send();
    }

    public function menu()
    {
        $this->chat->html('<b>مرحباً بك في نظام إدارة المزرعة 🐟</b>'."\n\n".'يرجى تحديد التقرير الذي ترغب في عرضه من القائمة أدناه:')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('💰 المبيعات')->action('sales'),
                Button::make('🌾 الحصاد')->action('harvest'),
                Button::make('⚠️ مخزون الأعلاف')->action('feedStock'),
                Button::make('🐟 الدورات النشطة')->action('batches'),
                Button::make('🍽️ منصرف الأعلاف')->action('dailyFeedIssues'),
                Button::make('💸العُهد - المصروفات')->action('expenses'),
                Button::make('🧾 الخزينة والقيود')->action('cashflow'),
                Button::make('💵 السلف')->action('advances'),
                Button::make('👥 الموظفين')->action('employees'),
                Button::make('📊 حسابات خارجية')->action('externalCalculations'),
                Button::make('🏗️ مصروفات المزارع')->action('farmExpenses'),
                Button::make('📄 تقرير اليوم بأكمله (PDF)')->action('report'),
            ]))->send();
    }

    public function external()
    {
        $this->externalCalculations();
    }

    public function externalCalculations()
    {
        $service = app(\App\Services\Telegram\ExternalCalculationReportService::class);
        $this->chat->html('<i>جاري جلب بيانات الحسابات الخارجية...</i> ⏳')->send();

        $data = $service->generateSummaryReport();

        $keyboard = Keyboard::make();
        if (isset($data['accounts']) && $data['accounts']->isNotEmpty()) {
            foreach ($data['accounts'] as $account) {
                $keyboard->button($account->name)->action('externalCalculationReport')->param('id', $account->id);
            }
        }

        if ($keyboard->isEmpty()) {
            $this->chat->html($data['html'] ?? 'لا توجد بيانات متاحة.')->send();
        } else {
            $this->chat->html($data['html'])->keyboard($keyboard)->send();
        }
    }

    public function externalCalculationReport(int $id)
    {
        $service = app(\App\Services\Telegram\ExternalCalculationReportService::class);
        $html = $service->generateAccountReport($id);

        $this->chat->html($html)->send();
    }

    public function farmExpenses()
    {
        $service = app(\App\Services\Telegram\FarmExpenseReportService::class);
        $this->chat->html('<i>جاري جلب بيانات مصروفات المزارع...</i> ⏳')->send();

        $data = $service->generateSummaryReport();

        $keyboard = Keyboard::make();
        if (isset($data['farms']) && $data['farms']->isNotEmpty()) {
            foreach ($data['farms'] as $farm) {
                $keyboard->button($farm->name)->action('farmExpenseReport')->param('id', $farm->id);
            }
        }

        if ($keyboard->isEmpty()) {
            $this->chat->html($data['html'] ?? 'لا توجد بيانات متاحة.')->send();
        } else {
            $this->chat->html($data['html'])->keyboard($keyboard)->send();
        }
    }

    public function farmExpenseReport(int $id)
    {
        $service = app(\App\Services\Telegram\FarmExpenseReportService::class);
        $html = $service->generateFarmReport($id);

        $this->chat->html($html)->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->html("<i>Sorry, I don't understand that command. Try /menu to see available options.</i>")->send();
    }
}
