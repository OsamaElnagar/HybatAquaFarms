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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trader_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            // Financial fields - calculated from harvest_boxes
            $table->decimal('boxes_subtotal', 12, 2)->default(0)->comment('مجموع الصناديق');
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('نسبة العمولة % (من التاجر أو مخصصة)');
            $table->decimal('commission_amount', 12, 2)->default(0)->comment('قيمة العمولة');
            $table->decimal('transport_cost', 12, 2)->default(0)->comment('تكلفة النقل والتعريبة');
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_before_commission', 12, 2)->default(0)->comment('الإجمالي قبل خصم العمولة');
            $table->decimal('net_amount', 12, 2)->default(0)->comment('الصافي بعد كل شيء');

            $table->string('payment_status')->default('pending'); // pending, partial, paid
            $table->string('delivery_status')->nullable(); // pending, in_transit, delivered
            $table->date('delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'date']);
            $table->index(['trader_id', 'date']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
