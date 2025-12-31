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
            $table->foreignId('harvest_operation_id')->constrained()->cascadeOnDelete()->comment('عملية الحصاد الأم');
            $table->date('harvest_date')->comment('تاريخ يوم الحصاد');
            $table->string('shift')->nullable()->comment('الفترة: morning, afternoon, night');
            $table->string('status')->default('pending')->comment('pending, in_progress, completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['harvest_operation_id', 'harvest_date']);
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
