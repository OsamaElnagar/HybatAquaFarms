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

        $this->info('Registering commands with Telegram API...');

        $bot->registerCommands([
            'menu' => 'عرض القائمة التفاعلية الرئيسية للتقارير',
            'report' => 'إنشاء وتحميل تقرير اليوم الكامل (PDF)',
            'sales' => 'الحصول على ملخص سريع لمبيعات هذا الشهر',
            'batches' => 'التحقق من الدورات النشطة حالياً',
            'harvest' => 'عرض إجمالي عمليات الحصاد لهذا الشهر',
            'expenses' => 'عرض إجمالي مصروفات السندات لهذا الشهر',
            'cashflow' => 'عرض التدفقات النقدية والقيود',
            'advances' => 'التحقق من سلف الموظفين المتبقية',
            'feedStock' => 'تنبيهات نقص المخزون في مستودعات الأعلاف',
        ])->send();

        $this->info('Commands registered successfully! Open your Telegram app and type "/" to see the menu.');

        return self::SUCCESS;
    }
}
