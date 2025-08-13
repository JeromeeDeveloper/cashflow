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
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashflow_file_id')->constrained('cashflow_files')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('gl_account_id')->constrained('gl_accounts')->onDelete('cascade');
            // Period information
            $table->year('year');
            $table->string('month', 20); // January, February, etc.
            $table->string('period')->nullable(); // Q1, Q2, etc. if needed

            // Account information
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable(); // Asset, Liability, Equity, Income, Expense
            $table->string('cashflow_category')->nullable(); // Operating, Investing, Financing

            // Amount fields
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->decimal('projection_percentage', 8, 2)->nullable();
            $table->decimal('projected_amount', 15, 2)->nullable();

            // Cash projection plan - dynamic months or periods
            $table->json('period_values')->nullable();

            // Totals and sections with numeric values
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('cash_beginning_balance', 15, 2)->nullable();
            $table->decimal('total_cash_available', 15, 2)->nullable();
            $table->decimal('less_disbursements', 15, 2)->nullable();
            $table->decimal('total_disbursements', 15, 2)->nullable();
            $table->decimal('cash_ending_balance', 15, 2)->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
