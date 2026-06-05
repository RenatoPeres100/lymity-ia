<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'agent_task_id')) {
                $table->unsignedBigInteger('agent_task_id')->nullable()->after('ai_employee_id');
            }
            if (!Schema::hasColumn('blog_posts', 'agent_task_run_id')) {
                $table->unsignedBigInteger('agent_task_run_id')->nullable()->after('agent_task_id');
            }
            if (!Schema::hasColumn('blog_posts', 'generated_content_package_id')) {
                $table->unsignedBigInteger('generated_content_package_id')->nullable()->after('agent_task_run_id');
            }
        });

        Schema::table('social_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_posts', 'agent_task_id')) {
                $table->unsignedBigInteger('agent_task_id')->nullable()->after('ai_employee_id');
            }
            if (!Schema::hasColumn('social_posts', 'agent_task_run_id')) {
                $table->unsignedBigInteger('agent_task_run_id')->nullable()->after('agent_task_id');
            }
            if (!Schema::hasColumn('social_posts', 'generated_content_package_id')) {
                $table->unsignedBigInteger('generated_content_package_id')->nullable()->after('agent_task_run_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            foreach (['agent_task_id', 'agent_task_run_id', 'generated_content_package_id'] as $col) {
                if (Schema::hasColumn('blog_posts', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('social_posts', function (Blueprint $table) {
            foreach (['agent_task_id', 'agent_task_run_id', 'generated_content_package_id'] as $col) {
                if (Schema::hasColumn('social_posts', $col)) $table->dropColumn($col);
            }
        });
    }
};
