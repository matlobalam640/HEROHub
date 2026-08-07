<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->string('photo_id_path')->nullable()->after('notes');
            $table->string('passport_path')->nullable()->after('photo_id_path');
        });
    }

    public function down(): void
    {
        Schema::table('membership_coverage_profiles', function (Blueprint $table) {
            $table->dropColumn(['photo_id_path', 'passport_path']);
        });
    }
};
