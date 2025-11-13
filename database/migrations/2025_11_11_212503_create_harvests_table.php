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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->string('harvest_number')->unique();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('farm_units')->nullOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete()->comment('رقم أمر البيع المرتبط');
            $table->date('harvest_date');
            $table->integer('boxes_count')->comment('عدد الصناديق/الأقفاص');
            $table->decimal('total_weight', 10, 3)->comment('الوزن الإجمالي بالكيلو جرام');
            $table->decimal('average_weight_per_box', 10, 3)->nullable()->comment('متوسط وزن الصندوق');
            $table->integer('total_quantity')->comment('إجمالي عدد الأسماك المحصودة');
            $table->decimal('average_fish_weight', 10, 3)->nullable()->comment('متوسط وزن السمكة بالجرام');
            $table->string('status')->default('pending')->comment('pending, completed, sold');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'harvest_date']);
            $table->index(['farm_id', 'harvest_date']);
            $table->index('sales_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
