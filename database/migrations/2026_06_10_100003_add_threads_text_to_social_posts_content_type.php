<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add threads_text to content_type ENUM
        DB::statement("ALTER TABLE social_posts MODIFY content_type ENUM('feed','reels','story','carousel','short_video','article','thread','threads_text')");
    }

    public function down(): void
    {
        // Note: removing a value from ENUM requires confirming no rows use it
        DB::statement("ALTER TABLE social_posts MODIFY content_type ENUM('feed','reels','story','carousel','short_video','article','thread')");
    }
};
