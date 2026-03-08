<?php

namespace App\Console\Commands;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class TestTelegramReportCommand extends Command
{
    protected $signature = 'reports:test {report? : The name of the report to test (sales, harvest, feed, batches, expenses, cashflow, advances, dailyFeedIssues, employees, farmExpenses)}';

    protected $description = 'Test generating and sending a specific Telegram report';

    public function handle()
    {
        $reportType = $this->argument('report');

        if (! $reportType) {
            $reportType = $this->choice(
                'Which report would you like to test?',
                ['sales', 'harvest', 'feed', 'batches', 'expenses', 'cashflow', 'advances', 'dailyFeedIssues', 'employees', 'farmExpenses'],
                0
            );
        }

        $this->info("Generating {$reportType} Report...");
        $html = '';
        $keyboard = null;

        switch ($reportType) {
            case 'sales':
                $html = app(\App\Services\Telegram\SalesReportService::class)->generateReport();
                break;
            case 'harvest':
                $html = app(\App\Services\Telegram\HarvestReportService::class)->generateReport();
                break;
            case 'feed':
                $data = app(\App\Services\Telegram\FeedStockReportService::class)->generateSummaryReport();
                $html = $data['html'];

                $keyboard = \DefStudio\Telegraph\Keyboard\Keyboard::make();
                foreach ($data['warehouses'] as $warehouse) {
                    $name = $warehouse->name;
                    if ($warehouse->farm) {
                        $name .= ' ('.$warehouse->farm->name.')';
                    }
                    $keyboard->button($name)->action('warehouseStock')->param('id', $warehouse->id);
                }
                break;
            case 'batches':
                $data = app(\App\Services\Telegram\BatchReportService::class)->generateActiveBatchesReport();
                $html = $data['html'];

                $keyboard = \DefStudio\Telegraph\Keyboard\Keyboard::make();
                if (isset($data['batches']) && $data['batches']->isNotEmpty()) {
                    foreach ($data['batches'] as $batch) {
                        $name = $batch->batch_code;
                        if ($batch->farm) {
                            $name .= ' ('.$batch->farm->name.')';
                        }
                        $keyboard->button($name)->action('batchReport')->param('id', $batch->id);
                    }
                }
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
            case 'dailyFeedIssues':
                $html = app(\App\Services\Telegram\DailyFeedIssueReportService::class)->generateReport();
                break;
            case 'employees':
                $data = app(\App\Services\Telegram\EmployeeReportService::class)->generateSummaryReport();
                $html = $data['html'];

                $keyboard = \DefStudio\Telegraph\Keyboard\Keyboard::make();
                if (isset($data['employees']) && $data['employees']->isNotEmpty()) {
                    foreach ($data['employees'] as $employee) {
                        $name = $employee->name;
                        if ($employee->farm) {
                            $name .= ' ('.$employee->farm->name.')';
                        }
                        $keyboard->button($name)->action('employeeReport')->param('id', $employee->id);
                    }
                }
                break;
            case 'farmExpenses':
                $data = app(\App\Services\Telegram\FarmExpenseReportService::class)->generateSummaryReport();
                $html = $data['html'];

                $keyboard = \DefStudio\Telegraph\Keyboard\Keyboard::make();
                if (isset($data['farms']) && $data['farms']->isNotEmpty()) {
                    foreach ($data['farms'] as $farm) {
                        $keyboard->button($farm->name)->action('farmExpenseReport')->param('id', $farm->id);
                    }
                }
                break;
            default:
                $this->error('Invalid report type.');

                return self::FAILURE;
        }

        $chats = TelegraphChat::all();

        if ($chats->isEmpty()) {
            $this->warn('No Telegram chats registered.');

            return self::FAILURE;
        }

        $this->info('Sending to Telegram...');

        foreach ($chats as $chat) {
            $message = $chat->html($html);

            if ($keyboard) {
                $message->keyboard($keyboard);
            }

            $message->send();
            $this->info("Sent to chat ID: {$chat->chat_id}");
        }

        $this->info('Report test completed successfully.');

        return self::SUCCESS;
    }
}
