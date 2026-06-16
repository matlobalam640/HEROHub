<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->unique('billing_subscription_id', 'memberships_billing_subscription_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropUnique('memberships_billing_subscription_id_unique');
        });
    }
};
