<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('category', 'visitor')->update(['category' => 'retail']);
    }

    public function down(): void
    {
        // No safe reversal without recording prior values.
    }
};
