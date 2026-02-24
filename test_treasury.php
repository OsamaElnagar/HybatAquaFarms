<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$account1150 = \App\Models\Account::where('code', '1150')->first();
$account5210 = \App\Models\Account::where('code', '5210')->first();

echo "Account 1150 (Employee Advances):\n";
echo "Name: {$account1150->name}\n";
echo "Is Treasury: " . ($account1150->is_treasury ? 'Yes' : 'No') . "\n\n";

echo "Account 5210 (Salaries & Wages):\n";
echo "Name: {$account5210->name}\n";
echo "Is Treasury: " . ($account5210->is_treasury ? 'Yes' : 'No') . "\n\n";

$latestRepayment = \App\Models\AdvanceRepayment::latest()->first();

if ($latestRepayment) {
    echo "Latest Advance Repayment ID: {$latestRepayment->id}\n";
    $journalEntry = \App\Models\JournalEntry::where('source_type', $latestRepayment->getMorphClass())
        ->where('source_id', $latestRepayment->id)
        ->with('lines.account')
        ->first();

    if ($journalEntry) {
        echo "Journal Entry ID: {$journalEntry->id}\n";
        foreach ($journalEntry->lines as $line) {
            echo "- Line Account ID: {$line->account_id}, Code: {$line->account->code}, Name: {$line->account->name}, Is Treasury: " . ($line->account->is_treasury ? 'Yes' : 'No') . ", Debit: {$line->debit}, Credit: {$line->credit}\n";
        }
    } else {
        echo "No Journal Entry found for this repayment.\n";
    }
} else {
    echo "No Advance Repayments found.\n";
}
