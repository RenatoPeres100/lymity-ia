<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_task_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('agent_task_id');
            $table->unsignedBigInteger('ai_employee_id')->nullable();
            $table->unsignedBigInteger('execution_context_id')->nullable();
            $table->unsignedBigInteger('approval_request_id')->nullable();

            $table->string('status')->default('queued');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();

            $table->text('error_message')->nullable();

            $table->string('generated_entity_type')->nullable();
            $table->unsignedBigInteger('generated_entity_id')->nullable();

            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->decimal('estimated_cost_usd', 12, 6)->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('agent_task_id');
            $table->index('ai_employee_id');
            $table->index('company_id');
            $table->index('status');
            $table->index('scheduled_for');

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('agent_task_id')->references('id')->on('agent_tasks')->cascadeOnDelete();
            $table->foreign('ai_employee_id')->references('id')->on('ai_employees')->nullOnDelete();
            $table->foreign('execution_context_id')->references('id')->on('ai_execution_contexts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_task_runs');
    }
};
