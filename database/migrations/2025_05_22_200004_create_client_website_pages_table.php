<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_website_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_website_id')->constrained('client_websites')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->enum('page_type', ['home', 'about', 'services', 'contact', 'blog', 'landing', 'other'])->default('other');
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['client_website_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_website_pages');
    }
};
