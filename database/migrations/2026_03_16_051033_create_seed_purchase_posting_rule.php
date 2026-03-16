<?php

use App\Models\Account;
use App\Models\PostingRule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $account = Account::where('code', '1200')->first();

        if ($account) {
            PostingRule::updateOrCreate(
                ['event_key' => 'seed.purchase'],
                [
                    'name' => 'Seed Purchase',
                    'description' => 'شراء زريعة من المفرخات',
                    'debit_account_id' => $account->id,
                    'credit_account_id' => null, // Dynamic per factory
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PostingRule::where('event_key', 'seed.purchase')->delete();
    }
};
