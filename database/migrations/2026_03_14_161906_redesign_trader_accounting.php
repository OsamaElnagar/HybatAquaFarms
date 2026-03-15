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
        Schema::table('traders', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
        });

        // Data migration
        $tradersAccount = DB::table('accounts')->where('code', '1140')->first();
        if ($tradersAccount) {
            $traders = DB::table('traders')->get();
            foreach ($traders as $trader) {
                // 1. Create Sub-Account for Trade
                $accountId = DB::table('accounts')->insertGetId([
                    'parent_id' => $tradersAccount->id,
                    'code' => '1140.'.$trader->id,
                    'name' => 'تاجر: '.$trader->name,
                    'type' => $tradersAccount->type,
                    'is_active' => true,
                    'is_treasury' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Assign to Trader
                DB::table('traders')->where('id', $trader->id)->update(['account_id' => $accountId]);

                // 3. Migrate Sales Orders
                $salesOrderIds = DB::table('sales_orders')->where('trader_id', $trader->id)->pluck('id');
                if ($salesOrderIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\SalesOrder')
                        ->whereIn('source_id', $salesOrderIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $tradersAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 4. Migrate Vouchers (Receipts)
                $voucherIds = DB::table('vouchers')
                    ->where('counterparty_type', 'App\Models\Trader')
                    ->where('counterparty_id', $trader->id)
                    ->pluck('id');
                if ($voucherIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\Voucher')
                        ->whereIn('source_id', $voucherIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $tradersAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 5. Migrate Clearing Entries
                $clearingEntryIds = DB::table('clearing_entries')
                    ->where('trader_id', $trader->id)
                    ->pluck('id');
                if ($clearingEntryIds->isNotEmpty()) {
                    $jeIds = DB::table('journal_entries')
                        ->where('source_type', 'App\Models\ClearingEntry')
                        ->whereIn('source_id', $clearingEntryIds)
                        ->pluck('id');

                    if ($jeIds->isNotEmpty()) {
                        DB::table('journal_lines')
                            ->whereIn('journal_entry_id', $jeIds)
                            ->where('account_id', $tradersAccount->id)
                            ->update(['account_id' => $accountId]);
                    }
                }

                // 6. Migrate Partner Loans & Repayments (Moving them from Liabiility 2130 to Asset Sub-Account)
                $partnerLoanAccount = DB::table('accounts')->where('code', '2130')->first();
                if ($partnerLoanAccount) {
                    $loanIds = DB::table('partner_loans')
                        ->where('loanable_type', 'App\Models\Trader')
                        ->where('loanable_id', $trader->id)
                        ->pluck('id');

                    if ($loanIds->isNotEmpty()) {
                        // Update loan lines
                        $jeIds = DB::table('journal_entries')
                            ->where('source_type', 'App\Models\PartnerLoan')
                            ->whereIn('source_id', $loanIds)
                            ->pluck('id');

                        if ($jeIds->isNotEmpty()) {
                            DB::table('journal_lines')
                                ->whereIn('journal_entry_id', $jeIds)
                                ->where('account_id', $partnerLoanAccount->id)
                                ->update(['account_id' => $accountId]);
                        }

                        // Update repayment lines
                        $repaymentIds = DB::table('partner_loan_repayments')
                            ->whereIn('partner_loan_id', $loanIds)
                            ->pluck('id');

                        if ($repaymentIds->isNotEmpty()) {
                            $jeIdsRepayments = DB::table('journal_entries')
                                ->where('source_type', 'App\Models\PartnerLoanRepayment')
                                ->whereIn('source_id', $repaymentIds)
                                ->pluck('id');

                            if ($jeIdsRepayments->isNotEmpty()) {
                                DB::table('journal_lines')
                                    ->whereIn('journal_entry_id', $jeIdsRepayments)
                                    ->where('account_id', $partnerLoanAccount->id)
                                    ->update(['account_id' => $accountId]);
                            }
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
        Schema::table('traders', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
