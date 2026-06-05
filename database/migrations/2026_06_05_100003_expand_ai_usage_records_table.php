<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_records', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_usage_records', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ai_usage_records', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('ai_usage_records', 'ai_employee_id')) {
                $table->unsignedBigInteger('ai_employee_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('ai_usage_records', 'agent_task_id')) {
                $table->unsignedBigInteger('agent_task_id')->nullable()->after('ai_employee_id');
            }
            if (!Schema::hasColumn('ai_usage_records', 'agent_task_run_id')) {
                $table->unsignedBigInteger('agent_task_run_id')->nullable()->after('agent_task_id');
            }
            if (!Schema::hasColumn('ai_usage_records', 'status')) {
                $table->string('status')->default('recorded')->after('estimated_cost_usd');
            }
            if (!Schema::hasColumn('ai_usage_records', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_records', function (Blueprint $table) {
            foreach (['company_id', 'client_id', 'ai_employee_id', 'agent_task_id', 'agent_task_run_id', 'status', 'metadata'] as $col) {
                if (Schema::hasColumn('ai_usage_records', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
