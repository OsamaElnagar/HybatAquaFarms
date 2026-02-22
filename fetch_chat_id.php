<?php

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Http;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bot = TelegraphBot::first();
if (! $bot) {
    echo "No bot found.\n";
    exit;
}

$response = Http::get('https://api.telegram.org/bot'.$bot->token.'/getUpdates');
$data = $response->json();

if (isset($data['result']) && count($data['result']) > 0) {
    foreach ($data['result'] as $result) {
        if (isset($result['message']['chat'])) {
            $chatData = $result['message']['chat'];
            $chatId = $chatData['id'];
            $chatName = $chatData['first_name'] ?? 'Admin';

            $chat = $bot->chats()->firstOrCreate(
                ['chat_id' => $chatId],
                ['name' => $chatName]
            );

            echo 'Chat ID Registered: '.$chat->chat_id.' for '.$chat->name."\n";
            exit;
        }
    }
}
echo "No recent messages found to extract Chat ID from. Did you send a message to the bot?\n";
