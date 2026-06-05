<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('ai_employee_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->string('title');
            $table->string('task_type')->default('custom');
            $table->text('description')->nullable();
            $table->longText('operational_instructions')->nullable();
            $table->json('expected_output')->nullable();

            $table->string('content_channel')->nullable();
            $table->string('content_format')->nullable();

            $table->boolean('requires_external_research')->default(false);
            $table->text('external_research_topics')->nullable();
            $table->integer('external_research_recency_days')->nullable();
            $table->boolean('require_sources')->default(false);
            $table->string('if_research_unavailable')->default('ask_human');

            $table->boolean('requires_image')->default(false);
            $table->string('image_type')->nullable();
            $table->integer('carousel_slides_count')->nullable();
            $table->text('image_instructions')->nullable();

            $table->boolean('requires_approval')->default(true);
            $table->boolean('auto_schedule_after_approval')->default(true);
            $table->boolean('auto_publish_after_approval')->default(false);

            $table->string('frequency')->default('manual');
            $table->json('days_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->time('time_of_day')->nullable();
            $table->string('timezone')->default('America/Sao_Paulo');

            $table->integer('content_quantity_per_run')->default(1);
            $table->integer('max_runs_per_day')->default(1);

            $table->string('status')->default('active');

            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->integer('failure_count')->default(0);

            $table->string('task_context_hash', 64)->nullable();
            $table->text('compact_task_context')->nullable();
            $table->timestamp('compact_task_context_generated_at')->nullable();
            $table->unsignedInteger('version')->default(1);

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('client_id');
            $table->index('ai_employee_id');
            $table->index('task_type');
            $table->index('content_channel');
            $table->index('status');
            $table->index('next_run_at');
            $table->index('frequency');

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('ai_employee_id')->references('id')->on('ai_employees')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tasks');
    }
};
