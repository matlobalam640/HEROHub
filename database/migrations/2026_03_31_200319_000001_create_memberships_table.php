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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('membership_number')->unique(); // human-friendly ID shown on card
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('account_user_id')->nullable()->constrained('users')->nullOnDelete(); // customer login
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();  // business membership
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();   // reseller attribution

            $table->date('coverage_starts_on')->nullable();
            $table->date('coverage_ends_on')->nullable();
            $table->boolean('auto_renew')->default(true);

            $table->string('status')->default('inactive'); // inactive, active, expired, cancelled

            // Billing integration placeholders (Stripe/Zoho later)
            $table->string('billing_provider')->nullable(); // stripe, zoho
            $table->string('billing_customer_id')->nullable();
            $table->string('billing_subscription_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
