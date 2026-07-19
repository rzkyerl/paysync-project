<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the ghost `review` value from the payrolls.status enum.
     * Migrate any existing `review` records to `needs_review` first.
     */
    public function up(): void
    {
        DB::table('payrolls')
            ->where('status', 'review')
            ->update(['status' => 'needs_review']);

        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'needs_review',
                'pending_approval',
                'approved',
                'disbursed',
            ])->default('draft')->change();
        });
    }

    /**
     * Restore the `review` value to the enum (data migration is not reversed
     * as `needs_review` is functionally equivalent).
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'review',
                'needs_review',
                'pending_approval',
                'approved',
                'disbursed',
            ])->default('draft')->change();
        });
    }
};
