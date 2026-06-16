<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership_plan_change_stripe_sessions')) {
            return;
        }

        Schema::create('membership_plan_change_stripe_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('interval', 20);
            $table->string('stripe_checkout_session_id');
            $table->unsignedInteger('amount_total_cents')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique('stripe_checkout_session_id', 'mpcss_stripe_session_uidx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_change_stripe_sessions');
    }
};
