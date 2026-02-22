<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailyFarmReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:daily-sales';

    protected $description = 'Generate and send the daily sales and harvest report via Telegram';

    public function handle(\App\Services\PdfService $pdfService)
    {
        // 1. Generate Report Data
        // For demonstration purposes, we are creating a summary.
        // In a real scenario, we would query the Database for today's specific metrics.
        $headers = ['Metric', 'Value'];
        $rows = [
            ['Today\'s Sales', '$'.number_format(rand(1000, 5000), 2)],
            ['Total Harvested', rand(100, 500).' kg'],
            ['New Customers', rand(1, 10)],
            ['Pending Orders', rand(0, 5)],
        ];

        $reportTitle = 'Daily Summary - '.now()->format('Y-m-d');

        $this->info('Generating PDF Report...');

        $pdf = $pdfService->generateReportPdf($reportTitle, $headers, $rows);

        // Save PDF temporarily to storage
        $pdfPath = storage_path('app/temp-daily-report.pdf');
        file_put_contents($pdfPath, $pdf->output());

        $this->info('Sending to Telegram...');

        // 2. Send via Telegraph to all registered chats
        $chats = \DefStudio\Telegraph\Models\TelegraphChat::all();

        if ($chats->isEmpty()) {
            $this->warn('No Telegram chats registered.');

            return self::FAILURE;
        }

        foreach ($chats as $chat) {
            $chat->document($pdfPath, 'daily-report-'.now()->format('Y-m-d').'.pdf')
                ->message("📊 *Daily Report Summary*\nHere is your requested daily report for ".now()->format('Y-m-d'))
                ->send();

            $this->info("Sent to chat ID: {$chat->chat_id}");
        }

        // Clean up temp file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        $this->info('Daily report process completed successfully.');

        return self::SUCCESS;
    }
}
