<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agency_brand_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('brand_name');
            $table->text('positioning')->nullable();
            $table->text('tone_of_voice')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('main_services')->nullable();
            $table->text('forbidden_terms')->nullable();
            $table->text('preferred_terms')->nullable();
            $table->text('cta_examples')->nullable();
            $table->text('content_guidelines')->nullable();
            $table->text('visual_guidelines')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_brand_contexts');
    }
};
