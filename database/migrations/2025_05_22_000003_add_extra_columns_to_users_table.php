<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->string('role')->nullable()->after('password');
            $table->string('user_type')->nullable()->after('role');
            $table->string('job_title')->nullable()->after('user_type');
            $table->string('status')->default('active')->after('job_title');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn(['company_id', 'client_id', 'role', 'user_type', 'job_title', 'status', 'last_login_at']);
        });
    }
};
