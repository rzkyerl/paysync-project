<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the redundant `status` and `joined_at` columns from the `employees`
     * table. The canonical columns are `work_status` and `join_date`.
     *
     * Before dropping, ensure the canonical columns carry the latest data so
     * nothing is silently lost (handles the `update()` divergence bug).
     */
    public function up(): void
    {
        // Sync canonical columns from the aliases where the canonical is null/empty.
        DB::table('employees')->whereNull('work_status')
            ->whereNotNull('status')
            ->update(['work_status' => DB::raw('status')]);

        DB::table('employees')->whereNull('join_date')
            ->whereNotNull('joined_at')
            ->update(['join_date' => DB::raw('joined_at')]);

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['status', 'joined_at']);
        });
    }

    /**
     * Restore the alias columns and re-populate them from the canonical ones.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', ['active', 'probation', 'contract', 'inactive'])
                ->nullable()
                ->after('position');
            $table->date('joined_at')->nullable()->after('status');
        });

        DB::table('employees')->update([
            'status'    => DB::raw('work_status'),
            'joined_at' => DB::raw('join_date'),
        ]);
    }
};
