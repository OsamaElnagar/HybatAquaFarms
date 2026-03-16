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
        Schema::table('external_calculation_entries', function (Blueprint $table) {
            $table->foreignId('external_calculation_statement_id')
                ->nullable()
                ->after('external_calculation_id')
                ->constrained('external_calculation_statements')
                ->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('external_calculation_statement_id')
                ->nullable()
                ->after('factory_statement_id')
                ->constrained('external_calculation_statements')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('external_calculation_statement_id');
        });

        Schema::table('external_calculation_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('external_calculation_statement_id');
        });
    }
};
