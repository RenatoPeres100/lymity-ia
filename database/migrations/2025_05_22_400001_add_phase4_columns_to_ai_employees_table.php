<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_employees', 'autonomy_level')) {
                $table->enum('autonomy_level', ['low', 'medium', 'high'])->default('low')->after('description');
            }
            if (!Schema::hasColumn('ai_employees', 'default_client_id')) {
                $table->foreignId('default_client_id')->nullable()->constrained('clients')->nullOnDelete()->after('autonomy_level');
            }
            if (!Schema::hasColumn('ai_employees', 'max_tasks_per_day')) {
                $table->integer('max_tasks_per_day')->default(5)->after('default_client_id');
            }
            if (!Schema::hasColumn('ai_employees', 'requires_approval_default')) {
                $table->boolean('requires_approval_default')->default(true)->after('max_tasks_per_day');
            }
        });

        // Rename status enum to include 'disabled' if not already
        // We'll just add 'disabled' support by modifying the column
        DB::statement("ALTER TABLE ai_employees MODIFY COLUMN status ENUM('active','paused','inactive','disabled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::table('ai_employees', function (Blueprint $table) {
            $table->dropForeign(['default_client_id']);
            $table->dropColumn(['autonomy_level', 'default_client_id', 'max_tasks_per_day', 'requires_approval_default']);
        });

        DB::statement("ALTER TABLE ai_employees MODIFY COLUMN status ENUM('active','paused','inactive') NOT NULL DEFAULT 'active'");
    }
};
