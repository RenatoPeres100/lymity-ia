<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand status enum for full pipeline
        DB::statement("ALTER TABLE blog_posts MODIFY COLUMN status ENUM(
            'draft','pending_approval','approved','scheduled','publishing',
            'published','failed','rejected','archived'
        ) NOT NULL DEFAULT 'draft'");

        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('blog_posts', 'publication_error')) {
                $table->text('publication_error')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('blog_posts', 'ai_employee_id')) {
                $table->foreignId('ai_employee_id')->nullable()->after('author_id')
                    ->constrained('ai_employees')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('ai_employee_id');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('blog_posts', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            foreach (['scheduled_at', 'publication_error', 'approved_at'] as $col) {
                if (Schema::hasColumn('blog_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('blog_posts', 'ai_employee_id')) {
                $table->dropForeign(['ai_employee_id']);
                $table->dropColumn('ai_employee_id');
            }
            if (Schema::hasColumn('blog_posts', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
        });

        DB::statement("ALTER TABLE blog_posts MODIFY COLUMN status ENUM(
            'draft','pending_approval','approved','published','archived'
        ) NOT NULL DEFAULT 'draft'");
    }
};
