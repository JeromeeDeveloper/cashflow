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
        Schema::table('users', function (Blueprint $table) {
            // Change the default value of status column from 'active' to 'inactive'
            // This ensures new users are inactive by default until they log in
            $table->string('status')->default('inactive')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to 'active' as default
            $table->string('status')->default('active')->change();
        });
    }
};
