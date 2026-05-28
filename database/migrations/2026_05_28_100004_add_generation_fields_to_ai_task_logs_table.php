<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_task_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_task_logs', 'task_type')) {
                $table->string('task_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ai_task_logs', 'status')) {
                $table->string('status')->nullable()->after('task_type');
            }
            if (!Schema::hasColumn('ai_task_logs', 'input_tokens')) {
                $table->unsignedInteger('input_tokens')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'output_tokens')) {
                $table->unsignedInteger('output_tokens')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'estimated_cost_usd')) {
                $table->decimal('estimated_cost_usd', 12, 6)->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'model')) {
                $table->string('model')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'provider')) {
                $table->string('provider')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'finished_at')) {
                $table->timestamp('finished_at')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'payload_snapshot')) {
                $table->json('payload_snapshot')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'error_message')) {
                $table->text('error_message')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'related_type')) {
                $table->string('related_type')->nullable();
            }
            if (!Schema::hasColumn('ai_task_logs', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_task_logs', function (Blueprint $table) {
            $cols = ['task_type','status','input_tokens','output_tokens','estimated_cost_usd',
                     'model','provider','started_at','finished_at','payload_snapshot',
                     'error_message','related_type','related_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('ai_task_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
