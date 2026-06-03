<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('insight_type');
            $table->string('title');
            $table->text('content');
            $table->integer('score')->nullable();
            $table->json('payload')->nullable();
            $table->string('model')->nullable();
            $table->string('provider')->nullable();
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->decimal('estimated_cost_usd', 10, 6)->nullable();
            $table->timestamps();

            $table->index('prospect_lead_id');
            $table->index('company_id');
            $table->index('ai_employee_id');
            $table->index('insight_type');
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_ai_insights');
    }
};
