<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional Zoho Billing product codes and legacy hosted-checkout URL columns (URLs unused; checkout is Stripe-only).
     * Populate zoho_code_* via admin or seeders as needed for webhook mapping.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('zoho_code_monthly', 96)->nullable()->after('billing_interval');
            $table->string('zoho_code_yearly', 96)->nullable()->after('zoho_code_monthly');
            $table->text('checkout_url_monthly')->nullable()->after('zoho_code_yearly');
            $table->text('checkout_url_yearly')->nullable()->after('checkout_url_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'zoho_code_monthly',
                'zoho_code_yearly',
                'checkout_url_monthly',
                'checkout_url_yearly',
            ]);
        });
    }
};
