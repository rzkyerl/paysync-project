<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('nip');
            $table->unsignedTinyInteger('days_present')->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->unsignedTinyInteger('leave_days')->default(0);
            $table->timestamps();
            $table->unique(['payroll_id', 'employee_id']);
            $table->index(['company_id', 'payroll_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
