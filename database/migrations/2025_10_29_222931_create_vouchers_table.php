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
        Schema::create("vouchers", function (Blueprint $table) {
            $table->id();
            $table->foreignId("farm_id")->constrained()->cascadeOnDelete();
            $table
                ->foreignId("batch_id")
                ->nullable()
                ->constrained("batches")
                ->nullOnDelete()
                ->comment("ربط المصروف بدورة إنتاج محددة");
            $table->string("voucher_type"); // receipt, payment
            $table->string("voucher_number"); // unique per farm+type
            $table->date("date");
            $table->morphs("counterparty"); // employee, trader, factory, driver, supplier
            $table
                ->foreignId("petty_cash_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->decimal("amount", 12, 2);
            $table->text("description")->nullable();
            $table->string("payment_method")->nullable(); // cash, bank, check
            $table->string("reference_number")->nullable();
            $table
                ->foreignId("created_by")
                ->nullable()
                ->constrained("users")
                ->nullOnDelete();
            $table->text("notes")->nullable();
            $table->timestamps();

            $table->unique(["farm_id", "voucher_type", "voucher_number"]);
            $table->index(["farm_id", "date"]);
            $table->index("voucher_type");
            $table->index("batch_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vouchers");
    }
};
