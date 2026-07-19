<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'invited'])->default('active')->after('role');
            $table->string('invitation_token')->nullable()->unique()->after('status');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['invitation_token']);
            $table->dropColumn(['status', 'invitation_token', 'invitation_expires_at']);
        });
    }
};
