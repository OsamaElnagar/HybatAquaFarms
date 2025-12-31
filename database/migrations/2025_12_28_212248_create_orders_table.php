<?php

use App\Models\Driver;
use App\Models\Harvest;
use App\Models\HarvestOperation;
use App\Models\Trader;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignIdFor(HarvestOperation::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Harvest::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Trader::class)->constrained()->nullOnDelete();
            // sales_order_id removed - pivot table used instead
            $table->foreignIdFor(Driver::class)->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['code', 'date']);
            $table->unique(['code', 'date'], 'orders_code_date_unique');
            // orders_harvest_op_unique removed to allow multiple orders per harvest operation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
