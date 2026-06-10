<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE approval_requests MODIFY approval_type ENUM('post','campaign','budget','blog','website_page','proposal','external_action','ai_task','other','ai_content','threads_text_post')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE approval_requests MODIFY approval_type ENUM('post','campaign','budget','blog','website_page','proposal','external_action','ai_task','other','ai_content')");
    }
};
