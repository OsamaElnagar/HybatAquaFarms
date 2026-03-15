<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trader_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('opened_at');
            $table->date('closed_at')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['trader_id', 'status']);
        });

        // Link journal entries to a trader statement session
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('trader_statement_id')
                ->nullable()
                ->after('is_posted')
                ->constrained('trader_statements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trader_statement_id');
        });

        Schema::dropIfExists('trader_statements');
    }
};
