<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_briefs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('title');
            $table->string('topic');
            $table->text('goal')->nullable();
            $table->text('audience')->nullable();
            $table->string('primary_keyword')->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->enum('funnel_stage', ['awareness', 'consideration', 'conversion', 'retention'])->default('awareness');
            $table->enum('search_intent', ['informational', 'commercial', 'transactional', 'navigational'])->default('informational');
            $table->json('outline')->nullable();
            $table->text('cta_suggestion')->nullable();
            $table->enum('status', ['draft', 'generated', 'approved', 'used', 'archived'])->default('draft');
            $table->timestamp('scheduled_for')->nullable();
            $table->unsignedBigInteger('generated_by_agent_id')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('generated_by_agent_id')->references('id')->on('ai_employees')->nullOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_briefs');
    }
};
