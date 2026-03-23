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
        Schema::create('egg_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('egg_collection_id')->constrained('egg_collections')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('trader_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_cash_sale')->default(false);
            $table->date('sale_date');
            $table->integer('trays_sold')->default(0);
            $table->integer('eggs_per_tray')->default(30);
            $table->integer('total_eggs')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_sales');
    }
};
