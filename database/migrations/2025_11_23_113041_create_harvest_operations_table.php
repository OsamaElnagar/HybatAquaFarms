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
        Schema::create('harvest_operations', function (Blueprint $table) {
            $table->id();
            $table->string('operation_number')->unique()->comment('رقم العملية');
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete()->comment('الدفعة المحصودة');
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->date('start_date')->comment('تاريخ بدء الحصاد');
            $table->date('end_date')->nullable()->comment('تاريخ انتهاء الحصاد');
            $table->string('status')->default('planned')->comment('planned, ongoing, paused, completed, cancelled');
            $table->integer('estimated_duration_days')->nullable()->comment('المدة المتوقعة بالأيام');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['farm_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_operations');
    }
};
