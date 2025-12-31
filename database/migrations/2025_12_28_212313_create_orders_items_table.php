<?php

use App\Models\Box;
use App\Models\Order;
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
        Schema::create('orders_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Box::class)->constrained()->nullOnDelete();

            $table->integer('quantity'); // Number of boxes
            $table->decimal('weight_per_box', 10, 2)->nullable(); // Avg weight
            $table->decimal('total_weight', 10, 2)->nullable(); // Total for this line

            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0); // quantity * weight * price OR quantity * price

            $table->timestamps();

            // Prevent duplicate box types per order
            $table->unique(['order_id', 'box_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_items');
    }
};
