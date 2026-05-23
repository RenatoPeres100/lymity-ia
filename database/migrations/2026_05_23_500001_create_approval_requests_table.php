<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_employee_id')->nullable()->constrained('ai_employees')->nullOnDelete();
            $table->nullableMorphs('approvable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('approval_type', [
                'post', 'campaign', 'budget', 'blog', 'website_page',
                'proposal', 'external_action', 'ai_task', 'other',
            ])->default('other');
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'changes_requested', 'canceled',
            ])->default('pending');
            $table->enum('sensitive_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->json('payload')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
