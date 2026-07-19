<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->text('rejection_note')->nullable()->after('approved_by');
            $table->timestamp('approved_at')->nullable()->after('rejection_note');
            $table->timestamp('disbursed_at')->nullable()->after('approved_at');
            $table->string('disbursement_proof')->nullable()->after('disbursed_at');
            $table->foreignId('disbursed_by')->nullable()->after('disbursement_proof')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn(['rejection_note', 'approved_at', 'disbursed_at', 'disbursement_proof', 'disbursed_by']);
        });
    }
};
