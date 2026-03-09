<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_loan_id')->constrained('partner_loans')->cascadeOnDelete();
            $table->string('repayment_type');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->foreignId('treasury_account_id')->nullable()->constrained('accounts');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_loan_repayments');
    }
};
