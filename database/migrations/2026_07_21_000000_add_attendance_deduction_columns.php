<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom untuk mendukung potongan absensi:
 *
 * attendance_records:
 *   - work_days  : jumlah hari kerja normal dalam periode (default 22)
 *
 * payroll_items:
 *   - absence_deduction : potongan akibat tidak hadir (proporsional dari gaji pokok)
 *   - days_present_snapshot : snapshot hari hadir saat kalkulasi
 *   - work_days_snapshot    : snapshot hari kerja periode saat kalkulasi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('work_days')->default(22)->after('leave_days');
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('absence_deduction', 15, 2)->default(0)->after('overtime_pay');
            $table->unsignedTinyInteger('days_present_snapshot')->default(0)->after('absence_deduction');
            $table->unsignedTinyInteger('work_days_snapshot')->default(22)->after('days_present_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('work_days');
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['absence_deduction', 'days_present_snapshot', 'work_days_snapshot']);
        });
    }
};
