<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_image_generations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();

            // Polymorphic relation to the content item this generation belongs to
            $table->nullableMorphs('related');

            $table->string('channel')->default('general');     // blog | social | general
            $table->string('purpose')->default('other');       // featured_image | social_single | social_carousel | blog_cover | blog_inline | other
            $table->string('generation_type')->default('single'); // single | carousel

            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->longText('prompt_context')->nullable();
            $table->longText('final_prompt')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('tone')->nullable();
            $table->string('visual_style')->nullable();
            $table->longText('brand_context')->nullable();

            $table->string('overlay_mode')->nullable();   // none | safe_top | safe_bottom | safe_left | safe_right | center_free
            $table->string('overlay_text')->nullable();

            $table->integer('slides_count')->default(1);
            $table->string('aspect_ratio')->nullable();   // 1:1 | 4:5 | 16:9

            $table->string('status')->default('draft');   // draft | queued | generating | completed | failed | archived

            $table->string('provider')->nullable()->index();
            $table->string('model')->nullable()->index();

            $table->json('request_metadata')->nullable();
            $table->json('response_metadata')->nullable();

            $table->decimal('estimated_cost', 12, 8)->nullable();
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->integer('duration_ms')->nullable();

            $table->longText('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            $table->index('channel');
            $table->index('purpose');
            $table->index('generation_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_image_generations');
    }
};
