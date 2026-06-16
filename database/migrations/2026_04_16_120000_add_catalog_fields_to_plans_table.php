<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('tier')->nullable()->after('category'); // local, vip
            $table->string('retail_subgroup')->nullable()->after('tier'); // 10_day, 1_month, annual_individual, annual_family
            $table->unsignedInteger('sort_order')->default(0)->after('retail_subgroup');
            $table->decimal('price_monthly', 10, 2)->nullable()->after('price');
            $table->json('features')->nullable()->after('price_monthly');
            $table->string('ideal_for')->nullable()->after('features');
            $table->unsignedTinyInteger('included_members')->nullable()->after('ideal_for');
            $table->decimal('addon_price_yearly', 10, 2)->nullable()->after('included_members');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'tier',
                'retail_subgroup',
                'sort_order',
                'price_monthly',
                'features',
                'ideal_for',
                'included_members',
                'addon_price_yearly',
            ]);
        });
    }
};
