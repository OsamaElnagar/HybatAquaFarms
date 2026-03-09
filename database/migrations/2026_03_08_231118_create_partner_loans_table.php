<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_loans', function (Blueprint $table) {
            $table->id();
            $table->morphs('loanable');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->foreignId('treasury_account_id')->constrained('accounts');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_loans');
    }
};
