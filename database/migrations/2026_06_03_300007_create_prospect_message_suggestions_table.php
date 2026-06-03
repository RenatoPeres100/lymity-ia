<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_message_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('objective');
            $table->text('message');
            $table->string('status')->default('draft');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('prospect_lead_id');
            $table->index('company_id');
            $table->index('ai_employee_id');
            $table->index('channel');
            $table->index('objective');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_message_suggestions');
    }
};
