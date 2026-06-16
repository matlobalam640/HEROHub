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
        Schema::create('partner_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->decimal('sale_amount', 10, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->default(8.00);
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_sales');
    }
};
