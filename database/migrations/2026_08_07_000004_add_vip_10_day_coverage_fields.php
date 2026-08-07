<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->string('mailing_street')->nullable()->after('occupation');
            $table->string('mailing_city')->nullable()->after('mailing_street');
            $table->string('mailing_state')->nullable()->after('mailing_city');
            $table->string('mailing_zip_code')->nullable()->after('mailing_state');
            $table->string('mailing_country')->nullable()->after('mailing_zip_code');
            $table->string('passport_issued_by')->nullable()->after('mailing_country');
            $table->json('trip_details')->nullable()->after('passport_issued_by');
            $table->json('travel_preferences')->nullable()->after('trip_details');
            $table->string('applicant_signature')->nullable()->after('travel_preferences');
            $table->date('signature_date')->nullable()->after('applicant_signature');
        });
    }

    public function down(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'mailing_street',
                'mailing_city',
                'mailing_state',
                'mailing_zip_code',
                'mailing_country',
                'passport_issued_by',
                'trip_details',
                'travel_preferences',
                'applicant_signature',
                'signature_date',
            ]);
        });
    }
};
