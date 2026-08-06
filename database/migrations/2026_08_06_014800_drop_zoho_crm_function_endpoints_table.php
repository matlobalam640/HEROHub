<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('zoho_crm_function_endpoints');
    }

    public function down(): void
    {
        // No rollback needed: this is a legacy cleanup migration.
    }
};
