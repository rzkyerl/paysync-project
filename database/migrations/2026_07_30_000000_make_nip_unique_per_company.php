<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce per-company uniqueness on NIP (employees.nip).
 *
 * Previously nip was globally unique, which prevented two different
 * companies from using the same NIP value. The application treats NIP
 * as a per-company identifier, so the constraint must reflect that.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the old global unique index on nip
            $table->dropUnique('employees_nip_unique');

            // Add a composite unique index scoped to company_id
            $table->unique(['company_id', 'nip', 'deleted_at'], 'employees_company_nip_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_company_nip_unique');
            $table->unique('nip');
        });
    }
};
