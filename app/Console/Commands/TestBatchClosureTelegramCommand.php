<?php

namespace App\Console\Commands;

use App\Models\Batch;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class TestBatchClosureTelegramCommand extends Command
{
    protected $signature = 'telegraph:test-batch-closure {batch? : The ID of the batch to test}';

    protected $description = 'Test generating and sending the batch closure Telegram notification';

    public function handle()
    {
        $batchId = $this->argument('batch');

        if ($batchId) {
            $batch = Batch::find($batchId);
            if (! $batch) {
                $this->error("Batch with ID {$batchId} not found.");

                return self::FAILURE;
            }
        } else {
            $batch = Batch::latest('id')->first();
            if (! $batch) {
                $this->error('No batches found in the database to test with.');

                return self::FAILURE;
            }
            $this->info("No batch ID provided. Using the latest batch: {$batch->batch_code} (ID: {$batch->id})");
        }

        $chats = TelegraphChat::all();

        if ($chats->isEmpty()) {
            $this->warn('No Telegram chats registered.');

            return self::FAILURE;
        }

        $currency = function ($value) {
            return number_format((float) $value).' EGP';
        };

        $message = "🐟 <b><u>إغلاق دورة إنتاج جديدة (اختبار)</u></b> 🐟\n\n".
            "🏷 <b>كود الدورة:</b> <code>{$batch->batch_code}</code>\n".
            "👤 <b>بواسطة:</b> <code>Test User</code>\n".
            "━━━━━━━━━━━━━━━━━━\n".
            "💰 <b>إجمالي التكاليف الأساسية:</b> {$currency($batch->total_cycle_expenses)}\n".
            "💵 <b>إجمالي الإيرادات الأساسية:</b> {$currency($batch->total_revenue)}\n";

        if (! empty($batch->misc_transactions)) {
            $message .= "━━━━━━━━━━━━━━━━━━\n📋 <b>التسويات الإضافية:</b>\n";
            foreach ($batch->misc_transactions as $tx) {
                $icon = $tx['type'] === 'revenue' ? '🟢' : '🔴';
                $message .= "{$icon} {$tx['description']}: {$currency($tx['amount'])}\n";
            }
        }

        $message .= "━━━━━━━━━━━━━━━━━━\n".
            "📈 <b>صافي الربح:</b> {$currency($batch->net_profit)}\n".
            '📊 <b>هامش الربح:</b> '.number_format((float) $batch->profit_margin, 2)."%\n\n".
            '<i>تم حفظ البيانات وإقفال الدورة بنجاح.</i>';

        $this->info("Generated Message:\n");
        $this->line(strip_tags($message));
        $this->info("\nSending to Telegram...");

        foreach ($chats as $chat) {
            $chat->html($message)->send();
            $this->info("Sent to chat ID: {$chat->chat_id}");
        }

        $this->info('Batch closure notification test completed successfully.');

        return self::SUCCESS;
    }
}
