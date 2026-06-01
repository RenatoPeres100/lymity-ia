<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('social_channels', 'profile_picture_url')) {
                $table->string('profile_picture_url', 1000)->nullable()->after('account_url');
            }
            if (!Schema::hasColumn('social_channels', 'refresh_due_at')) {
                $table->timestamp('refresh_due_at')->nullable()->after('token_expires_at');
            }
            if (!Schema::hasColumn('social_channels', 'last_refreshed_at')) {
                $table->timestamp('last_refreshed_at')->nullable()->after('refresh_due_at');
            }
            if (!Schema::hasColumn('social_channels', 'disconnected_at')) {
                $table->timestamp('disconnected_at')->nullable()->after('last_error');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_channels', function (Blueprint $table) {
            foreach (['profile_picture_url', 'refresh_due_at', 'last_refreshed_at', 'disconnected_at'] as $col) {
                if (Schema::hasColumn('social_channels', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
