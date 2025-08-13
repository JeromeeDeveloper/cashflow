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
        Schema::create('cashflow_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('original_name'); // Original uploaded filename
            $table->string('file_type')->default('cashflow');
            $table->year('year'); // Year the file represents
            $table->string('month', 20)->nullable(); // Month if applicable (January, February, etc.)
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('uploaded_by')->nullable(); // User who uploaded
            $table->string('status')->default('pending'); // pending, processed, error
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_files');
    }
};
