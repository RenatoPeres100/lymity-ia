<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generated_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ai_image_generation_id')
                ->constrained('ai_image_generations')
                ->cascadeOnDelete()
                ->index();

            $table->integer('slide_number')->nullable()->index();
            $table->string('label')->nullable();

            $table->text('storage_path');
            $table->text('public_url');

            $table->string('mime_type')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->longText('prompt_used')->nullable();
            $table->text('alt_text')->nullable();

            $table->string('status')->default('active')->index(); // active | replaced | discarded | failed

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generated_images');
    }
};
