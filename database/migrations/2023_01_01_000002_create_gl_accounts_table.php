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
        Schema::create('gl_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique(); // Unique account code
            $table->string('account_name'); // Account name
            $table->foreignId('parent_id')->nullable()->constrained('gl_accounts')->onDelete('cascade'); // Parent account for hierarchical structure
            $table->string('account_type')->default('detail'); // 'parent' or 'detail'
            $table->integer('level')->default(0); // Hierarchy level (0 = root, 1 = child, etc.)
            $table->boolean('is_active')->default(true); // Whether account is active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gl_accounts');
    }
};
