<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_tasks', 'task_type')) {
                $table->string('task_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('ai_tasks', 'requires_approval')) {
                $table->boolean('requires_approval')->default(true)->after('task_type');
            }
            if (!Schema::hasColumn('ai_tasks', 'sensitive_action')) {
                $table->boolean('sensitive_action')->default(false)->after('requires_approval');
            }
            if (!Schema::hasColumn('ai_tasks', 'output')) {
                $table->longText('output')->nullable()->after('finished_at');
            }
            if (!Schema::hasColumn('ai_tasks', 'error_message')) {
                $table->longText('error_message')->nullable()->after('output');
            }
            if (!Schema::hasColumn('ai_tasks', 'metadata')) {
                $table->json('metadata')->nullable()->after('error_message');
            }
        });

        // Expand status enum to include new values
        DB::statement("ALTER TABLE ai_tasks MODIFY COLUMN status ENUM('draft','queued','running','waiting_approval','approved','rejected','completed','failed','canceled','pending_approval') NOT NULL DEFAULT 'queued'");

        // Expand priority enum to include 'medium' (was 'normal')
        DB::statement("ALTER TABLE ai_tasks MODIFY COLUMN priority ENUM('low','normal','medium','high','urgent') NOT NULL DEFAULT 'medium'");
    }

    public function down(): void
    {
        Schema::table('ai_tasks', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'requires_approval', 'sensitive_action', 'output', 'error_message', 'metadata']);
        });
    }
};
