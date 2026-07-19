<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->decimal('gross_pay', 15, 2)->default(0);
            $table->decimal('basic_salary_snapshot', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('bpjs_tk_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_kesehatan_deduction', 15, 2)->default(0);
            $table->decimal('pph21_deduction', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            $table->enum('status', ['pending', 'transferred'])->default('pending');
            $table->timestamp('disbursed_at')->nullable();
            $table->boolean('has_anomaly')->default(false);
            $table->string('anomaly_type')->nullable();
            $table->boolean('anomaly_acknowledged')->default(false);
            $table->timestamps();
            $table->unique(['payroll_id', 'employee_id']);
            $table->index(['company_id', 'payroll_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
