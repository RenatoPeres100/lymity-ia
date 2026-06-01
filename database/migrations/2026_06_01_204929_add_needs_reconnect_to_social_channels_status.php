<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE social_channels MODIFY COLUMN status ENUM(
            'not_configured','disconnected','connected','expired','needs_reconnect','error','active','inactive'
        ) NOT NULL DEFAULT 'not_configured'");
    }

    public function down(): void
    {
        DB::table('social_channels')
            ->where('status', 'needs_reconnect')
            ->update(['status' => 'expired']);

        DB::statement("ALTER TABLE social_channels MODIFY COLUMN status ENUM(
            'not_configured','disconnected','connected','expired','error','active','inactive'
        ) NOT NULL DEFAULT 'not_configured'");
    }
};
