<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->enum('status', ['active', 'probation', 'contract', 'inactive'])
                ->default('active')
                ->after('position');
            $table->date('joined_at')->nullable()->after('status');
        });

        // Keep the new fields aligned with records created before this migration.
        DB::table('employees')->update([
            'status' => DB::raw('work_status'),
            'joined_at' => DB::raw('join_date'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'status', 'joined_at']);
        });
    }
};
