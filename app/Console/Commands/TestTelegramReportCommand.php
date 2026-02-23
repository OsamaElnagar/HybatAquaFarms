<?php

namespace App\Console\Commands;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class TestTelegramReportCommand extends Command
{
    protected $signature = 'reports:test {report? : The name of the report to test (sales, harvest, feed, batches, expenses, cashflow, advances)}';

    protected $description = 'Test generating and sending a specific Telegram report';

    public function handle()
    {
        $reportType = $this->argument('report');

        if (! $reportType) {
            $reportType = $this->choice(
                'Which report would you like to test?',
                ['sales', 'harvest', 'feed', 'batches', 'expenses', 'cashflow', 'advances'],
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
