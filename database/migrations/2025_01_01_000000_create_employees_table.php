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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('name');
            $table->string('department');
            $table->string('position');
            $table->enum('work_status', ['active', 'probation', 'contract', 'inactive']);
            $table->date('join_date');
            $table->enum('bank_account_status', ['verified', 'unverified', 'rejected']);
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
