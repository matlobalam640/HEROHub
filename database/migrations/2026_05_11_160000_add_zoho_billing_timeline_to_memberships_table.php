<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->timestamp('billing_subscription_created_at')->nullable()->after('billing_subscription_id');
            $table->date('billing_next_billing_at')->nullable()->after('billing_subscription_created_at');
            $table->date('billing_last_billing_at')->nullable()->after('billing_next_billing_at');
            $table->boolean('billing_auto_collect')->nullable()->after('billing_last_billing_at');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn([
                'billing_subscription_created_at',
                'billing_next_billing_at',
                'billing_last_billing_at',
                'billing_auto_collect',
            ]);
        });
    }
};
