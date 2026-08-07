<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('nationality')->nullable()->after('country');
            $table->date('passport_expiry_date')->nullable()->after('id_number');
        });

        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->string('resident_status')->nullable()->after('preferred_coverage_start_date');
            $table->string('measurement_unit')->nullable()->after('resident_status');
            $table->string('height')->nullable()->after('measurement_unit');
            $table->string('weight')->nullable()->after('height');
            $table->string('occupation')->nullable()->after('weight');
            $table->json('health_questionnaire')->nullable()->after('medical_condition_flags');
        });
    }

    public function down(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'resident_status',
                'measurement_unit',
                'height',
                'weight',
                'occupation',
                'health_questionnaire',
            ]);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'passport_expiry_date']);
        });
    }
};
