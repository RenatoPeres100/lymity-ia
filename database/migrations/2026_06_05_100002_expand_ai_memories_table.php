<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_memories', function (Blueprint $table) {
            // Make ai_employee_id nullable
            if (Schema::hasColumn('ai_memories', 'ai_employee_id')) {
                $table->unsignedBigInteger('ai_employee_id')->nullable()->change();
            }

            if (!Schema::hasColumn('ai_memories', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('ai_memories', 'agent_task_id')) {
                $table->unsignedBigInteger('agent_task_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('ai_memories', 'compact_content')) {
                $table->text('compact_content')->nullable()->after('content');
            }
            if (!Schema::hasColumn('ai_memories', 'relevance_score')) {
                $table->integer('relevance_score')->default(50)->after('weight');
            }
            if (!Schema::hasColumn('ai_memories', 'active')) {
                $table->boolean('active')->default(true)->after('relevance_score');
            }
            if (!Schema::hasColumn('ai_memories', 'source_type')) {
                $table->string('source_type')->nullable()->after('active');
            }
            if (!Schema::hasColumn('ai_memories', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('ai_memories', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('source_id');
            }
            if (!Schema::hasColumn('ai_memories', 'metadata')) {
                $table->json('metadata')->nullable()->after('expires_at');
            }
        });

        // Expand memory_type enum — we need to modify it
        // MySQL approach: add new column, copy, drop old, rename
        if (Schema::hasColumn('ai_memories', 'memory_type')) {
            \DB::statement("ALTER TABLE ai_memories MODIFY COLUMN memory_type VARCHAR(50) NOT NULL DEFAULT 'general'");
        }
    }

    public function down(): void
    {
        Schema::table('ai_memories', function (Blueprint $table) {
            foreach (['company_id', 'agent_task_id', 'compact_content', 'relevance_score', 'active', 'source_type', 'source_id', 'expires_at', 'metadata'] as $col) {
                if (Schema::hasColumn('ai_memories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
