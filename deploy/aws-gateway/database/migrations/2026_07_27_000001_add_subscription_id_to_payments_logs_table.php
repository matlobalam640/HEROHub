<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments_logs', function (Blueprint $table) {
            $table->string('subscription_id')->nullable()->after('transactionid');
            $table->decimal('amount', 10, 2)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('payments_logs', function (Blueprint $table) {
            $table->dropColumn(['subscription_id', 'amount']);
        });
    }
};
