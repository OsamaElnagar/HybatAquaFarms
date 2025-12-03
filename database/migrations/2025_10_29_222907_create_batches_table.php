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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table
                ->foreignId('unit_id')
                ->nullable()
                ->constrained('farm_units')
                ->nullOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table
                ->foreignId('factory_id')
                ->nullable()
                ->constrained('factories')
                ->nullOnDelete()
                ->comment('مصنع التفريخ/المفرخة');
            $table->date('entry_date');
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->decimal('initial_weight_avg', 10, 3)->nullable();
            $table->decimal('current_weight_avg', 10, 3)->nullable();
            $table
                ->decimal('unit_cost', 10, 2)
                ->nullable()
                ->comment('تكلفة الوحدة من المفرخة');
            $table
                ->decimal('total_cost', 12, 2)
                ->nullable()
                ->comment('التكلفة الإجمالية للزريعة');
            $table
                ->string('source')
                ->nullable()
                ->comment('hatchery/transfer/purchase');
            $table->string('status')->default('active'); // active, harvested, depleted
            $table
                ->boolean('is_cycle_closed')
                ->default(false)
                ->comment('هل تم إقفال دورة الإنتاج');
            $table
                ->date('closure_date')
                ->nullable()
                ->comment('تاريخ إقفال الدورة');
            $table
                ->decimal('total_feed_cost', 12, 2)
                ->nullable()
                ->comment('إجمالي تكلفة العلف المستهلك');
            $table
                ->decimal('total_operating_expenses', 12, 2)
                ->nullable()
                ->comment('إجمالي المصروفات التشغيلية');
            $table
                ->decimal('total_revenue', 12, 2)
                ->nullable()
                ->comment('إجمالي الإيرادات من المبيعات');
            $table
                ->decimal('net_profit', 12, 2)
                ->nullable()
                ->comment('صافي الربح');
            $table
                ->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('المستخدم الذي أقفل الدورة');
            $table
                ->text('closure_notes')
                ->nullable()
                ->comment('ملاحظات إقفال الدورة');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index('entry_date');
            $table->index('factory_id');
            $table->index('is_cycle_closed');
            $table->index('closure_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
