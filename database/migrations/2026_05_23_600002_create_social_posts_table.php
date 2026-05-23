<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_employee_id')->nullable()->constrained('ai_employees')->nullOnDelete();
            $table->string('title');
            $table->enum('objective', ['awareness', 'engagement', 'leads', 'sales', 'authority', 'relationship'])->default('authority');
            $table->enum('content_type', ['feed', 'reels', 'story', 'carousel', 'short_video', 'article', 'thread'])->default('feed');
            $table->longText('main_caption')->nullable();
            $table->longText('creative_brief')->nullable();
            $table->text('hashtags')->nullable();
            $table->text('cta')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'scheduled', 'published', 'rejected', 'archived'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
