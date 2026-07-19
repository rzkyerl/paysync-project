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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payrolls')->where('status', 'review')->update(['status' => 'needs_review']);

        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'needs_review',
                'pending_approval',
                'approved',
                'disbursed',
            ])->change();
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
