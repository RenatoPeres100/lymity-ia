<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_employee_id')->nullable()->constrained('ai_employees')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', [
                'draft', 'pending_approval', 'approved', 'rejected', 'sent', 'accepted', 'archived',
            ])->default('draft');
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
