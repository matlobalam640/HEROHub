<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('enrollment_status', 32)->default('active')->after('owner_user_id');
            $table->timestamp('activated_at')->nullable()->after('enrollment_status');
            $table->unsignedInteger('seat_limit')->nullable()->after('activated_at');
            $table->string('billing_provider', 40)->nullable()->after('seat_limit');
            $table->string('billing_subscription_id')->nullable()->after('billing_provider');
            $table->string('subscribed_interval', 20)->nullable()->after('billing_subscription_id');
            $table->timestamp('invited_at')->nullable()->after('subscribed_interval');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'enrollment_status',
                'activated_at',
                'seat_limit',
                'billing_provider',
                'billing_subscription_id',
                'subscribed_interval',
                'invited_at',
            ]);
        });
    }
};
