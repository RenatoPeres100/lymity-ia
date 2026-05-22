<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_brand_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->string('brand_name')->nullable();
            $table->text('brand_positioning')->nullable();
            $table->text('tone_of_voice')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('main_offer')->nullable();
            $table->text('objections')->nullable();
            $table->text('competitors')->nullable();
            $table->text('visual_style')->nullable();
            $table->text('forbidden_terms')->nullable();
            $table->text('preferred_terms')->nullable();
            $table->text('cta_examples')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_brand_profiles');
    }
};
