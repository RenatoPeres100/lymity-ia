<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand status enum to include pipeline states
        DB::statement("ALTER TABLE social_channels MODIFY COLUMN status ENUM(
            'not_configured','disconnected','connected','expired','error','active','inactive'
        ) NOT NULL DEFAULT 'not_configured'");

        Schema::table('social_channels', function (Blueprint $table) {
            if (!Schema::hasColumn('social_channels', 'instagram_user_id')) {
                $table->string('instagram_user_id')->nullable()->after('account_url');
            }
            if (!Schema::hasColumn('social_channels', 'facebook_page_id')) {
                $table->string('facebook_page_id')->nullable()->after('instagram_user_id');
            }
            if (!Schema::hasColumn('social_channels', 'external_account_id')) {
                $table->string('external_account_id')->nullable()->after('facebook_page_id');
            }
            if (!Schema::hasColumn('social_channels', 'permissions')) {
                $table->json('permissions')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('social_channels', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('permissions');
            }
            if (!Schema::hasColumn('social_channels', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_checked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('social_channels', function (Blueprint $table) {
            foreach (['instagram_user_id', 'facebook_page_id', 'external_account_id', 'permissions', 'last_checked_at', 'last_error'] as $col) {
                if (Schema::hasColumn('social_channels', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
