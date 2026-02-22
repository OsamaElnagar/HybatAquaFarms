<?php

namespace App\Http\Controllers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Stringable;

class TelegramWebhookHandler extends WebhookHandler
{
    public function sales()
    {
        $sales = '$'.number_format(rand(1000, 5000), 2);

        $this->chat->message("Today's sales so far: *{$sales}*")->send();
    }

    public function report()
    {
        $this->chat->message('Generating your report. Please wait...')->send();

        // Call the artisan command we built earlier!
        Artisan::call('reports:daily-sales');
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->message("Sorry, I don't understand that command. Try /sales or /report")->send();
    }
}
