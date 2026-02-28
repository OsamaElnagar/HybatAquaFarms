<?php

namespace App\Console\Commands;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Console\Command;

class RegisterTelegramCommands extends Command
{
    protected $signature = 'telegraph:register-menu';

    protected $description = 'Register the interactive Telegram commands for the Bot';

    public function handle()
    {
        $bot = TelegraphBot::first();

        if (!$bot) {
            $this->error('No TelegraphBot found in the database. Please register the bot first.');

            return self::FAILURE;
        }

        $this->info('Unregistering old commands...');
        $unregisterResponse = $bot->unregisterCommands()->send();
        if (!$unregisterResponse->successful()) {
            $this->error('Failed to unregister commands: ' . $unregisterResponse->body());
        }

        $this->info('Waiting 3 seconds to clear Telegram cache...');
        sleep(3);

        $this->info('Registering new commands with Telegram API...');

        $registerResponse = $bot->registerCommands([
            'menu' => 'عرض القائمة التفاعلية الرئيسية للتقارير',
            'report' => 'إنشاء وتحميل تقرير اليوم الكامل (PDF)',
            'sales' => 'الحصول على ملخص سريع لمبيعات هذا الشهر',
            'batches' => 'التحقق من الدورات النشطة حالياً',
            'harvest' => 'عرض إجمالي عمليات الحصاد لهذا الشهر',
            'expenses' => 'عرض إجمالي مصروفات السندات لهذا الشهر',
            'cashflow' => 'عرض التدفقات النقدية والقيود',
            'advances' => 'التحقق من سلف الموظفين المتبقية',
            'feedstock' => 'تنبيهات نقص المخزون في مستودعات الأعلاف',
            'dailyfeedissues' => 'تقرير المنصرف اليومي للأعلاف (آخر يومين)',
            'external' => 'عرض تقرير الحسابات الخارجية',
        ])->send();

        if (!$registerResponse->successful()) {
            $this->error('Failed to register commands: ' . $registerResponse->body());
            return self::FAILURE;
        }

        $this->info('Commands registered successfully! Open your Telegram app and type "/" to see the menu. If they do not appear, fully close and reopen your Telegram app to clear its local cache.');

        return self::SUCCESS;
    }
}
