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
            $table->integer('box_number')->comment('رقم الصندوق/القفص');
            $table->decimal('weight', 10, 3)->comment('وزن الصندوق بالكيلو جرام');
            $table->integer('fish_count')->nullable()->comment('عدد الأسماك في الصندوق');
            $table->decimal('average_fish_weight', 10, 3)->nullable()->comment('متوسط وزن السمكة بالجرام');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('harvest_id');
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
