<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prospect_pipeline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prospect_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('segment')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('source')->nullable();
            $table->string('interest_level')->nullable();
            $table->integer('fit_score')->nullable();
            $table->decimal('potential_value', 12, 2)->nullable();
            $table->string('estimated_budget')->nullable();
            $table->string('status')->default('open');
            $table->string('next_action')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->text('pain_points')->nullable();
            $table->text('opportunity_summary')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('client_id');
            $table->index('prospect_pipeline_id');
            $table->index('prospect_stage_id');
            $table->index('owner_id');
            $table->index('status');
            $table->index('interest_level');
            $table->index('fit_score');
            $table->index('next_follow_up_at');
            $table->index('last_contacted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_leads');
    }
};
