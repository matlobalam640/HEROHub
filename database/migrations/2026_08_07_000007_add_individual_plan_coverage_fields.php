<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->string('primary_care_provider')->nullable()->after('occupation');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            $table->string('emergency_contact_gender')->nullable()->after('emergency_contact_relationship');
            $table->string('health_plan_provider')->nullable()->after('insurance_company');
            $table->string('health_insurer')->nullable()->after('health_plan_provider');
        });
    }

    public function down(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'primary_care_provider',
                'emergency_contact_relationship',
                'emergency_contact_gender',
                'health_plan_provider',
                'health_insurer',
            ]);
        });
    }
};
