<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_enrollment_profiles', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('company_id');
            $table->string('enrollment_kind')->nullable()->after('business_name');
            $table->timestamp('small_business_submitted_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('company_enrollment_profiles', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'enrollment_kind', 'small_business_submitted_at']);
        });
    }
};
