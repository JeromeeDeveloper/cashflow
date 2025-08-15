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
            $table->unsignedBigInteger('merged_into')->nullable()->after('parent_id');
            $table->json('merged_from')->nullable()->after('merged_into');

            $table->foreign('merged_into')->references('id')->on('gl_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->dropForeign(['merged_into']);
            $table->dropColumn(['merged_into', 'merged_from']);
        });
    }
};
