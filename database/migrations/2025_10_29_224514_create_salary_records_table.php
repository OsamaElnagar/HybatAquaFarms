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
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('advances_deducted', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('pending'); // pending, paid
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate records for the same employee and exact period
            $table->unique(
                ['employee_id', 'pay_period_start', 'pay_period_end'],
                'salary_records_employee_period_unique',
            );

            $table->index(['employee_id', 'pay_period_start']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
