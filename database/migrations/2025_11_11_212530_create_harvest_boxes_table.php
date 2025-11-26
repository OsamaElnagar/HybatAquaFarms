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
        Schema::create('harvest_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('harvest_operation_id')->constrained()->cascadeOnDelete()->comment('Denormalized for queries');
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete()->comment('Denormalized');
            $table->foreignId('species_id')->constrained()->comment('نوع السمك');

            // Box identification
            $table->integer('box_number')->comment('رقم الصندوق/القفص');

            // Classification & Quality
            $table->string('classification')->nullable()->comment('التصنيف: بلطي، نمرة 1، نمرة 2، جامبو، خرط، إلخ');
            $table->string('grade')->nullable()->comment('الدرجة: A, B, C or 1, 2, 3');
            $table->string('size_category')->nullable()->comment('فئة الحجم: small, medium, large, jumbo');

            // Weight & Count
            $table->decimal('weight', 10, 3)->comment('وزن الصندوق بالكيلو جرام');
            $table->integer('fish_count')->nullable()->comment('عدد الأسماك في الصندوق');
            $table->decimal('average_fish_weight', 10, 3)->nullable()->comment('متوسط وزن السمكة بالجرام');

            // Sales Information
            $table->foreignId('trader_id')->nullable()->constrained()->nullOnDelete()->comment('التاجر الذي اشترى هذا الصندوق');
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete()->comment('أمر البيع المرتبط');
            $table->decimal('unit_price', 10, 2)->nullable()->comment('سعر الوحدة (للكيلو أو القطعة)');
            $table->string('pricing_unit')->default('kg')->comment('وحدة التسعير: kg, piece, box');
            $table->decimal('subtotal', 12, 2)->nullable()->comment('إجمالي سعر الصندوق');
            $table->boolean('is_sold')->default(false)->comment('هل تم البيع');
            $table->timestamp('sold_at')->nullable()->comment('تاريخ البيع');
            $table->integer('line_number')->nullable()->comment('رقم السطر في الفاتورة');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('harvest_id');
            $table->index(['harvest_operation_id', 'is_sold']);
            $table->index(['trader_id', 'sales_order_id']);
            $table->index(['batch_id', 'classification']);
            $table->unique(['harvest_id', 'box_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_boxes');
    }
};
