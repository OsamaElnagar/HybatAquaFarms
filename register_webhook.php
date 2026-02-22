<?php

use DefStudio\Telegraph\Models\TelegraphBot;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bot = TelegraphBot::first();
if (! $bot) {
    echo "No bot found.\n";
    exit;
}

$url = 'https://abc7-41-235-80-216.ngrok-free.app/telegraph/'.$bot->token.'/webhook';
echo 'Registering webhook implicitly at: '.$url."\n";
$result = $bot->registerWebhook()->send();
print_r($result->json());
