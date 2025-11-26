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
        Schema::create('traders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('trader_type')->nullable()->comment('wholesale/retail');
            $table->integer('payment_terms_days')->nullable();
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('نسبة العمولة %');
            $table->string('commission_type')->default('percentage')->comment('percentage, fixed_per_kg, none');
            $table->decimal('default_transport_cost_per_kg', 10, 2)->nullable()->comment('تكلفة النقل الافتراضية للكيلو');
            $table->decimal('default_transport_cost_flat', 10, 2)->nullable()->comment('تكلفة النقل الافتراضية الثابتة');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traders');
    }
};
