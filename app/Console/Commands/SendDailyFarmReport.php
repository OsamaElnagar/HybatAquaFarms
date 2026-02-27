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
        $this->info('Fetching real-time data...');

        // 1. Treasury
        $treasuryService = app(\App\Services\TreasuryService::class);
        $dailyTreasury = $treasuryService->getDailySummary();
        $totalBalance = $treasuryService->getTreasuryBalance();

        // 2. Sales
        $salesQuery = \App\Models\SalesOrder::query()->whereDate('order_date', now());
        $salesRevenue = (clone $salesQuery)->sum('total_after_discount');
        $salesOrdersCount = (clone $salesQuery)->count();
        // Assume selling weight from order items
        $soldWeight = \App\Models\OrderItem::query()
            ->whereHas('order.salesOrders', function ($q) {
                $q->whereDate('order_date', now());
            })->sum('total_weight');

        // 3. Harvest
        $harvestWeight = \App\Models\OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->whereDate('date', now());
            })->sum('total_weight');
        $harvestCount = \App\Models\Harvest::query()->whereDate('harvest_date', now())->count();

        // 4. Feed & Batches
        $feedQuery = \App\Models\DailyFeedIssue::query()->whereDate('date', now());
        $feedStats = (clone $feedQuery)
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('COUNT(DISTINCT batch_id) as active_batches')
            ->first();

        $feedCost = (clone $feedQuery)
            ->join('feed_items', 'daily_feed_issues.feed_item_id', '=', 'feed_items.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('daily_feed_issues.quantity * feed_items.standard_cost'));

        $data = [
            'date' => now()->format('Y-m-d'),
            'treasury' => [
                'balance' => $totalBalance,
                'incoming' => $dailyTreasury['incoming'] ?? 0,
                'outgoing' => $dailyTreasury['outgoing'] ?? 0,
            ],
            'sales' => [
                'revenue' => $salesRevenue,
                'orders_count' => $salesOrdersCount,
                'weight' => $soldWeight,
            ],
            'harvest' => [
                'weight' => $harvestWeight,
                'operations_count' => $harvestCount,
            ],
            'feed' => [
                'consumption' => $feedStats->total_quantity ?? 0,
                'cost' => $feedCost,
                'active_batches' => $feedStats->active_batches ?? 0,
            ],
        ];

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
