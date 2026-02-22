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

        if (! $bot) {
            $this->error('No TelegraphBot found in the database. Please register the bot first.');

            return self::FAILURE;
        }

        $this->info('Registering commands with Telegram API...');

        $bot->registerCommands([
            'menu' => 'Show the main interactive reports menu',
            'sales' => 'Get a quick summary of sales this month',
            'batches' => 'Check the number of active batches',
            'harvest' => 'View total harvest operations this month',
            'feed_stock' => 'Alerts for low feed stock across warehouses',
            'expenses' => 'View total voucher expenses this month',
            'advances' => 'Check remaining employee advances',
            'report' => 'Generate and download the Daily PDF Report',
        ])->send();

        $this->info('Commands registered successfully! Open your Telegram app and type "/" to see the menu.');

        return self::SUCCESS;
    }
}
