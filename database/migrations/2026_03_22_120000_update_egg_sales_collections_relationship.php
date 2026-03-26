<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egg_sales', function (Blueprint $table) {
            $table->dropForeign(['egg_collection_id']);
            $table->dropColumn('egg_collection_id');
        });

        Schema::table('egg_collections', function (Blueprint $table) {
            $table->foreignId('egg_sale_id')->nullable()->constrained('egg_sales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('egg_collections', function (Blueprint $table) {
            $table->dropForeign(['egg_sale_id']);
            $table->dropColumn('egg_sale_id');
        });

        Schema::table('egg_sales', function (Blueprint $table) {
            $table->foreignId('egg_collection_id')->constrained('egg_collections')->cascadeOnDelete();
        });
    }
};
