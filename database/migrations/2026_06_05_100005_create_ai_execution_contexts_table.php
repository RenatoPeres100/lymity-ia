<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_execution_contexts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('ai_employee_id')->nullable();
            $table->unsignedBigInteger('agent_task_id')->nullable();
            $table->unsignedBigInteger('brand_context_id')->nullable();

            $table->unsignedInteger('brand_context_version')->nullable();
            $table->string('brand_context_hash', 64)->nullable();
            $table->unsignedInteger('task_version')->nullable();
            $table->string('task_context_hash', 64)->nullable();

            $table->text('compact_brand_context')->nullable();
            $table->text('compact_task_context')->nullable();
            $table->json('selected_memories')->nullable();
            $table->json('external_research_snapshot')->nullable();

            $table->string('prompt_hash', 64)->nullable();
            $table->text('prompt_preview')->nullable();

            $table->string('model')->nullable();
            $table->string('provider')->nullable();
            $table->integer('input_tokens_estimated')->nullable();
            $table->integer('output_tokens_estimated')->nullable();
            $table->decimal('estimated_cost_usd', 12, 6)->nullable();

            $table->string('status')->default('prepared');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('agent_task_id');
            $table->index('ai_employee_id');

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('ai_employee_id')->references('id')->on('ai_employees')->nullOnDelete();
            $table->foreign('agent_task_id')->references('id')->on('agent_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_execution_contexts');
    }
};
