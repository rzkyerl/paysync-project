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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('period'); // e.g. "2026-07"
            $table->string('period_label'); // e.g. "Juli 2026"
            $table->enum('status', ['draft', 'needs_review', 'pending_approval', 'approved', 'disbursed']);
            $table->integer('employee_count')->default(0);
            $table->decimal('gross_total', 15, 2)->default(0);
            $table->decimal('deduction_total', 15, 2)->default(0);
            $table->decimal('net_total', 15, 2)->default(0);
            $table->integer('anomaly_count')->default(0);
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
