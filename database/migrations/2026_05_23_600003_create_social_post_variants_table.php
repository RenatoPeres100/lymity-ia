<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_post_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'linkedin', 'tiktok', 'threads', 'youtube', 'pinterest']);
            $table->longText('caption');
            $table->text('hashtags')->nullable();
            $table->text('cta')->nullable();
            $table->text('creative_notes')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'scheduled', 'published', 'rejected', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_variants');
    }
};
