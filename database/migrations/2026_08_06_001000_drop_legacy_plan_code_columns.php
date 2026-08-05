<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy billing-mapping column names kept for safe rollback compatibility.
     *
     * @var array<int, string>
     */
    private array $legacyPlanCodeColumns = ['zoho_code_monthly', 'zoho_code_yearly'];

    public function up(): void
    {
        $columns = $this->legacyPlanCodeColumns;

        Schema::table('plans', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        $columns = $this->legacyPlanCodeColumns;

        Schema::table('plans', function (Blueprint $table) use ($columns) {
            if (! Schema::hasColumn('plans', $columns[0])) {
                $table->string($columns[0], 96)->nullable()->after('billing_interval');
            }
            if (! Schema::hasColumn('plans', $columns[1])) {
                $table->string($columns[1], 96)->nullable()->after($columns[0]);
            }
        });
    }
};
