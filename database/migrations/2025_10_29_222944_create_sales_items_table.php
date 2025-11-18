<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("sales_items", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("sales_order_id")
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId("batch_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->comment("الدفعة المرتبطة");
            $table
                ->foreignId("species_id")
                ->constrained()
                ->comment("نوع السمك");
            $table->string("item_name")->nullable()->comment("اسم الصنف");
            $table->text("description")->nullable()->comment("وصف الصنف");

            // Quantity and Weight
            $table->decimal("quantity", 10, 2)->comment("العدد (قطعة/سمكة)");
            $table->decimal("weight_kg", 10, 3)->comment("الوزن بالكيلو جرام");
            $table
                ->decimal("average_fish_weight", 10, 3)
                ->nullable()
                ->comment("متوسط وزن السمكة (جرام)");

            // Quality Grading
            $table
                ->string("grade")
                ->nullable()
                ->comment("الدرجة/الجودة (A, B, C, etc.)");
            $table
                ->string("size_category")
                ->nullable()
                ->comment("فئة الحجم (صغير، متوسط، كبير، جامبو)");

            // Pricing
            $table
                ->decimal("unit_price", 10, 2)
                ->comment("سعر الوحدة (للكيلو أو القطعة)");
            $table
                ->string("pricing_unit")
                ->default("kg")
                ->comment("وحدة التسعير (kg, piece)");
            $table
                ->decimal("discount_percent", 5, 2)
                ->default(0)
                ->comment("نسبة الخصم %");
            $table
                ->decimal("discount_amount", 10, 2)
                ->default(0)
                ->comment("قيمة الخصم");
            $table->decimal("subtotal", 12, 2)->comment("المجموع قبل الخصم");
            $table
                ->decimal("total_price", 12, 2)
                ->comment("الإجمالي بعد الخصم");

            // Fulfillment tracking
            $table
                ->decimal("fulfilled_quantity", 10, 2)
                ->default(0)
                ->comment("الكمية المنفذة");
            $table
                ->decimal("fulfilled_weight", 10, 3)
                ->default(0)
                ->comment("الوزن المنفذ");
            $table
                ->string("fulfillment_status")
                ->default("pending")
                ->comment("حالة التنفيذ: pending, partial, fulfilled");

            // Additional Info
            $table
                ->integer("line_number")
                ->default(0)
                ->comment("رقم السطر في الطلب");
            $table->text("notes")->nullable();
            $table->timestamps();

            $table->index("sales_order_id");
            $table->index(["batch_id", "species_id"]);
            $table->index("fulfillment_status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sales_items");
    }
};
