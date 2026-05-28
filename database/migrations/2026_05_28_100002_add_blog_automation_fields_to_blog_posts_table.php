<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'content_brief_id')) {
                $table->unsignedBigInteger('content_brief_id')->nullable()->after('ai_employee_id');
                $table->foreign('content_brief_id')->references('id')->on('content_briefs')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'generated_by_agent_id')) {
                $table->unsignedBigInteger('generated_by_agent_id')->nullable()->after('content_brief_id');
                $table->foreign('generated_by_agent_id')->references('id')->on('ai_employees')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'approved_by_user_id')) {
                $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('generated_by_agent_id');
                $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'revision_notes')) {
                $table->text('revision_notes')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'ai_metadata')) {
                $table->json('ai_metadata')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('client_id');
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'secondary_keywords')) {
                $table->json('secondary_keywords')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $cols = ['content_brief_id', 'generated_by_agent_id', 'approved_by_user_id',
                     'revision_notes', 'ai_metadata', 'company_id', 'secondary_keywords'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('blog_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
