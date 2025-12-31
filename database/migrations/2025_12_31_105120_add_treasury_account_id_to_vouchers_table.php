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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('treasury_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()->comment('الطرف الآخر من المعاملة (مصروف/إيراد/عميل/مورد)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['treasury_account_id']);
            $table->dropColumn('treasury_account_id');
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
