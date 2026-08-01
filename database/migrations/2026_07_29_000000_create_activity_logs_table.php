<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Kategori: employee | payroll | attendance | team | settings
            $table->string('category', 30);

            // Kode event: employee.created, payroll.approved, dll
            $table->string('event', 60);

            // Deskripsi human-readable, e.g. "Import 15 karyawan dari CSV"
            $table->string('description');

            // Subject opsional (model yang terkait)
            $table->string('subject_type', 60)->nullable();  // e.g. "payroll"
            $table->unsignedBigInteger('subject_id')->nullable();  // e.g. payroll id

            // Metadata tambahan: JSON bebas
            $table->json('meta')->nullable();

            // IP address pencatat (opsional, untuk audit keamanan)
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['company_id', 'occurred_at']);
            $table->index(['company_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
