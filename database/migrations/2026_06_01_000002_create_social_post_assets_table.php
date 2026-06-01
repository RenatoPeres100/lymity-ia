<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_post_assets')) {
            Schema::create('social_post_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
                $table->string('type', 16)->default('image')->comment('image|video');
                $table->string('source', 20)->default('generated')->comment('generated|upload|external_url');
                $table->string('provider', 32)->nullable();
                $table->text('path')->nullable();
                $table->text('public_url');
                $table->unsignedTinyInteger('position')->default(1);
                $table->text('prompt')->nullable();
                $table->string('status', 20)->default('draft')
                    ->comment('draft|generating|generated|validating|valid|invalid|failed|published');
                $table->text('validation_error')->nullable();
                $table->string('instagram_container_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index('social_post_id');
                $table->index('status');
                $table->index('position');
                $table->index('instagram_container_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_assets');
    }
};
