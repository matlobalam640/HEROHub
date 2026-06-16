<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('default_plan_id')->nullable()->after('owner_user_id')->constrained('plans')->nullOnDelete();
            $table->decimal('billing_per_employee_override', 12, 2)->nullable()->after('default_plan_id');
            $table->unsignedInteger('billing_cached_active_employees')->default(0)->after('billing_per_employee_override');
            $table->decimal('billing_cached_monthly_total', 12, 2)->default(0)->after('billing_cached_active_employees');
            $table->timestamp('billing_calculated_at')->nullable()->after('billing_cached_monthly_total');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['default_plan_id']);
            $table->dropColumn([
                'default_plan_id',
                'billing_per_employee_override',
                'billing_cached_active_employees',
                'billing_cached_monthly_total',
                'billing_calculated_at',
            ]);
        });
    }
};
