<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zoho CRM Deluge "functions" execute URLs (no auth query string — key comes from ZOHO_CRM_API_KEY at runtime).
     */
    public function up(): void
    {
        Schema::create('zoho_crm_function_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->text('execute_url');
            $table->string('label', 160)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_crm_function_endpoints');
    }
};
