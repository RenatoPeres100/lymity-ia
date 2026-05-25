<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('ad_group_id')->nullable()->constrained('ad_groups')->cascadeOnDelete();
            $table->string('keyword');
            $table->enum('match_type', ['broad', 'phrase', 'exact', 'negative']);
            $table->enum('status', ['active', 'paused', 'negative', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_keywords');
    }
};
