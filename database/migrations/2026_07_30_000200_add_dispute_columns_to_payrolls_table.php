<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add dispute and reprocess columns to payrolls table.
 *
 * Supports the recall workflow: disbursed → disputed → approved
 * (after reprocess) → disburse again, or disputed → needs_review.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('dispute_reason', 2000)->nullable()->after('disbursement_proof');
            $table->foreignId('disputed_by')->nullable()->after('dispute_reason')->constrained('users')->nullOnDelete();
            $table->foreignId('reprocessed_by')->nullable()->after('disputed_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['disputed_by']);
            $table->dropForeign(['reprocessed_by']);
            $table->dropColumn(['dispute_reason', 'disputed_by', 'reprocessed_by']);
        });
    }
};
