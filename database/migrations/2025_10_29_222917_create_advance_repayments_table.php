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
        Schema::create('advance_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_advance_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method')->default('salary_deduction'); // salary_deduction, cash
            $table->foreignId('salary_record_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('balance_remaining', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_advance_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_repayments');
    }
};
