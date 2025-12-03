<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('petty_cash_id')
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId('batch_id')
                ->nullable()
                ->constrained('batches')
                ->nullOnDelete()
                ->comment('ربط المعاملة بدورة إنتاج محددة');
            $table
                ->foreignId('voucher_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table
                ->foreignId('expense_category_id')
                ->nullable()
                ->constrained('expense_categories')
                ->nullOnDelete();
            $table->date('date');
            $table->string('direction'); // in, out
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table
                ->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['petty_cash_id', 'date']);
            $table->index(['expense_category_id', 'direction']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};
