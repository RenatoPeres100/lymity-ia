<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['image', 'video', 'document', 'logo', 'brand_file', 'other'])->default('other');
            $table->string('path')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->enum('source', ['upload', 'google_drive', 'generated', 'external'])->default('upload');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_assets');
    }
};
