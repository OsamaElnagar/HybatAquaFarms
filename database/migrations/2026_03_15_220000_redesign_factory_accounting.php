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
        Schema::table('factories', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('type')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('factory_statement_id')->nullable()->after('trader_statement_id')->constrained('factory_statements')->nullOnDelete();
        });

        // Data migration
        $factoriesAccount = DB::table('accounts')->where('code', '2110')->first();
        if ($factoriesAccount) {
            $factories = DB::table('factories')->get();
            foreach ($factories as $factory) {
                // 1. Create Sub-Account for Factory (Liability)
                $accountId = DB::table('accounts')->insertGetId([
                    'parent_id' => $factoriesAccount->id,
                    'code' => '2110.'.$factory->id,
                    'name' => 'مصنع: '.$factory->name,
                    'type' => $factoriesAccount->type,
                    'is_active' => true,
                    'is_treasury' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Assign to Factory
                DB::table('factories')->where('id', $factory->id)->update(['account_id' => $accountId]);

                // 3. Create Initial Statement for Factory
                $statementId = DB::table('factory_statements')->insertGetId([
                    'factory_id' => $factory->id,
                    'title' => 'كشف الحساب الأول',
                    'opened_at' => now()->toDateString(),
                    'status' => 'open',
                    'opening_balance' => 0, // Legacy balance will be reflected via migrated JEs
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Migrate Feed Movements accounting entries
                // FeedMovements already post to 'feed.purchase' and 'feed.issue'.
                // We need to link 'feed.purchase' JEs to the factory statement.
                $feedMovementIds = DB::table('feed_movements')->where('factory_id', $factory->id)->pluck('id');
                if ($feedMovementIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\FeedMovement')
                        ->whereIn('source_id', $feedMovementIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_entries')
                            ->whereIn('id', $jeIds)
                            ->update(['factory_statement_id' => $statementId]);

                        // Update journal lines to use the factory's specific sub-account instead of the generic 2110
                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $factoriesAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 5. Migrate Factory Payments
                $paymentIds = DB::table('factory_payments')->where('factory_id', $factory->id)->pluck('id');
                if ($paymentIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\FactoryPayment')
                        ->whereIn('source_id', $paymentIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_entries')
                            ->whereIn('id', $jeIds)
                            ->update(['factory_statement_id' => $statementId]);

                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $factoriesAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 6. Migrate Vouchers
                $voucherIds = DB::table('vouchers')
                    ->where('counterparty_type', 'App\Models\Factory')
                    ->where('counterparty_id', $factory->id)
                    ->pluck('id');
                if ($voucherIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\Voucher')
                        ->whereIn('source_id', $voucherIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_entries')
                            ->whereIn('id', $jeIds)
                            ->update(['factory_statement_id' => $statementId]);

                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $factoriesAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 7. Migrate Partner Loans (if applicable to factory)
                $partnerLoanAccount = DB::table('accounts')->where('code', '2130')->first();
                if ($partnerLoanAccount) {
                    $loanIds = DB::table('partner_loans')
                        ->where('loanable_type', 'App\Models\Factory')
                        ->where('loanable_id', $factory->id)
                        ->pluck('id');

                    if ($loanIds->isNotEmpty()) {
                        $jeIds = DB::table('journal_entries')
                            ->where('source_type', 'App\Models\PartnerLoan')
                            ->whereIn('source_id', $loanIds)
                            ->pluck('id');

                        if ($jeIds->isNotEmpty()) {
                            DB::table('journal_entries')
                                ->whereIn('id', $jeIds)
                                ->update(['factory_statement_id' => $statementId]);

                            DB::table('journal_lines')
                                ->whereIn('journal_entry_id', $jeIds)
                                ->where('account_id', $partnerLoanAccount->id)
                                ->update(['account_id' => $accountId]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['factory_statement_id']);
            $table->dropColumn('factory_statement_id');
        });

        Schema::table('factories', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
