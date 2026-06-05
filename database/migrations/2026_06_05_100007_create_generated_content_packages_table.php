<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_content_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('agent_task_id')->nullable();
            $table->unsignedBigInteger('agent_task_run_id')->nullable();
            $table->unsignedBigInteger('ai_employee_id')->nullable();
            $table->unsignedBigInteger('approval_request_id')->nullable();

            $table->string('package_type')->default('blog_post');
            $table->string('title');
            $table->text('summary')->nullable();

            $table->json('content_payload');
            $table->json('visual_payload')->nullable();
            $table->json('sources_payload')->nullable();

            $table->string('status')->default('draft');

            $table->string('generated_entity_type')->nullable();
            $table->unsignedBigInteger('generated_entity_id')->nullable();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('agent_task_id');
            $table->index('status');
            $table->index('package_type');

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('agent_task_id')->references('id')->on('agent_tasks')->nullOnDelete();
            $table->foreign('agent_task_run_id')->references('id')->on('agent_task_runs')->nullOnDelete();
            $table->foreign('ai_employee_id')->references('id')->on('ai_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_content_packages');
    }
};
