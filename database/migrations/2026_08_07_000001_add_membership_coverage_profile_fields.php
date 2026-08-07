<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('street')->nullable()->after('city');
            $table->string('street_line2')->nullable()->after('street');
            $table->string('state')->nullable()->after('street_line2');
            $table->string('zip_code')->nullable()->after('state');
        });

        Schema::create('membership_coverage_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->unique()->constrained('memberships')->cascadeOnDelete();
            $table->date('preferred_coverage_start_date')->nullable();
            $table->string('emergency_contact_first_name')->nullable();
            $table->string('emergency_contact_last_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_effective_start')->nullable();
            $table->date('insurance_effective_end')->nullable();
            $table->string('insurance_member_id')->nullable();
            $table->string('insurance_policy_holder_name')->nullable();
            $table->string('insurance_policy_holder_relationship')->nullable();
            $table->string('insurance_beneficiary_name')->nullable();
            $table->string('insurance_beneficiary_relationship')->nullable();
            $table->string('insurance_provider_phone')->nullable();
            $table->string('insurance_plan_type')->nullable();
            $table->string('medevac_max_benefit')->nullable();
            $table->string('medevac_policy_number')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->json('medical_condition_flags')->nullable();
            $table->text('other_medical_info')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_coverage_profiles');

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['street', 'street_line2', 'state', 'zip_code']);
        });
    }
};
