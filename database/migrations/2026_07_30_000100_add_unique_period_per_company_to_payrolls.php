<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce per-company uniqueness on payroll period.
 *
 * Prevents duplicate payroll periods within the same company and
 * eliminates the race condition window that the application-level
 * check alone could not close.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->unique(['company_id', 'period'], 'payrolls_company_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_company_period_unique');
        });
    }
};
