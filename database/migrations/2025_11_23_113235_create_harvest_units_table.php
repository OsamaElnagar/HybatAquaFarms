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
        Schema::create('harvest_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('farm_units')->cascadeOnDelete();
            $table->integer('fish_count_before')->nullable()->comment('عدد الأسماك قبل الحصاد (تقديري)');
            $table->integer('fish_count_harvested')->comment('عدد الأسماك المحصودة من هذه الوحدة');
            $table->integer('fish_count_remaining')->nullable()->comment('المتبقي بعد الحصاد (تقديري)');
            $table->decimal('percentage_harvested', 5, 2)->nullable()->comment('نسبة الحصاد من الوحدة %');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('harvest_id');
            $table->index('unit_id');
            $table->unique(['harvest_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_units');
    }
};
