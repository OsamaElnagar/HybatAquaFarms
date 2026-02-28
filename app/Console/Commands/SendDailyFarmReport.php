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
        $this->info('Fetching comprehensive realtime data...');

        $dataService = app(\App\Services\Reports\ComprehensiveDailyReportDataService::class);
        $data = $dataService->gatherData();

        $this->info('Generating PDF Report...');

        $pdf = $pdfService->generateDailyFarmReportPdf($data);

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
                ->html("🌿 <b><u>DAILY FARM REPORT</u></b> 🌿\n\n".
                    '📅 <b>Date:</b> <code>'.now()->format('Y-m-d')."</code>\n".
                    "━━━━━━━━━━━━━━━━━━\n".
                    '📊 <i>Here is your requested daily summary.</i>')
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
