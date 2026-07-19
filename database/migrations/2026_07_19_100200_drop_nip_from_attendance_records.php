<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the redundant `nip` column from `attendance_records`.
     * The NIP is already derivable via the `employee_id` foreign key.
     * Keeping it risks data divergence if employees.nip is ever updated.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('nip');
        });
    }

    /**
     * Re-add the `nip` column and re-populate from the employees table.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('employee_id');
        });

        // Re-populate from employees table
        \Illuminate\Support\Facades\DB::statement('
            UPDATE attendance_records ar
            INNER JOIN employees e ON e.id = ar.employee_id
            SET ar.nip = e.nip
        ');
    }
};
