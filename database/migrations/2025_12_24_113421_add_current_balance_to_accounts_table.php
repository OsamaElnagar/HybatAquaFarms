<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('current_balance', 15, 2)->default(0)->after('type');
        });

        // Populate initial balances
        $accounts = \App\Models\Account::withSum('journalLines as total_debit', 'debit')
            ->withSum('journalLines as total_credit', 'credit')
            ->get();

        foreach ($accounts as $account) {
            $debit = $account->total_debit ?? 0;
            $credit = $account->total_credit ?? 0;

            if (in_array($account->type, [\App\Enums\AccountType::Asset, \App\Enums\AccountType::Expense])) {
                $balance = $debit - $credit;
            } else {
                $balance = $credit - $debit;
            }

            // Use updateQuietly if available, or just update directly as we don't have an observer yet that would interfere (AccountObserver usually doesn't watch balance changes this way)
            // But to be safe and efficient:
            DB::table('accounts')->where('id', $account->id)->update(['current_balance' => $balance]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('current_balance');
        });
    }
};
