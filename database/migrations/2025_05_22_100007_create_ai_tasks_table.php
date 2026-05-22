<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'running', 'completed', 'failed', 'canceled'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_task_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['info', 'warning', 'error', 'success'])->default('info');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('approval_type')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_approvals');
        Schema::dropIfExists('ai_task_logs');
        Schema::dropIfExists('ai_tasks');
    }
};
