<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            // Drop the existing unique constraint on account_code
            $table->dropUnique(['account_code']);

            // Add composite unique constraint on account_code + cashflow_type
            // This allows the same account_code to exist multiple times with different cashflow_type values
            $table->unique(['account_code', 'cashflow_type'], 'gl_accounts_account_code_cashflow_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('gl_accounts_account_code_cashflow_type_unique');

            // Restore the original unique constraint on account_code
            $table->unique(['account_code']);
        });
    }
};
