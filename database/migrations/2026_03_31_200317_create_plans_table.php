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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. IND_BASIC, FAM_VIP, VISITOR_10D, SMALL_BIZ
            $table->string('name');
            $table->string('category'); // retail, business, corporate
            $table->unsignedInteger('coverage_days')->nullable();
            $table->unsignedInteger('min_members')->nullable();   // business plans
            $table->unsignedInteger('max_members')->nullable();   // business plans
            $table->string('billing_interval')->nullable(); // monthly, yearly, one_time
            $table->unsignedInteger('commitment_months')->nullable(); // e.g. 12 for monthly w/ commitment
            $table->decimal('price', 10, 2)->nullable(); // pricing provided later
            $table->char('currency', 3)->default('USD');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
