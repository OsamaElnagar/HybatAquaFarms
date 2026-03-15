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
        Schema::table('trader_statements', function (Blueprint $table) {
            $table->string('title')->nullable()->after('trader_id');
        });

        Schema::create('harvest_operation_trader_statement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_operation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trader_statement_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_operation_trader_statement');

        Schema::table('trader_statements', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
