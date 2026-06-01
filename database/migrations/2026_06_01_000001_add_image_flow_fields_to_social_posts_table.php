<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('social_posts', 'image_generation_mode')) {
                $table->string('image_generation_mode', 32)->nullable()->after('public_image_url')
                    ->comment('none|ai_single|ai_carousel|upload|external_url');
            }
            if (!Schema::hasColumn('social_posts', 'image_prompt_source_hash')) {
                $table->string('image_prompt_source_hash', 64)->nullable()->after('image_prompt');
            }
            if (!Schema::hasColumn('social_posts', 'image_generated_from_caption_hash')) {
                $table->string('image_generated_from_caption_hash', 64)->nullable()->after('image_prompt_source_hash');
            }
            if (!Schema::hasColumn('social_posts', 'image_last_generated_at')) {
                $table->timestamp('image_last_generated_at')->nullable()->after('image_generated_from_caption_hash');
            }
            if (!Schema::hasColumn('social_posts', 'carousel_enabled')) {
                $table->boolean('carousel_enabled')->default(false)->after('image_last_generated_at');
            }
            if (!Schema::hasColumn('social_posts', 'carousel_slide_count')) {
                $table->unsignedTinyInteger('carousel_slide_count')->nullable()->after('carousel_enabled');
            }
            if (!Schema::hasColumn('social_posts', 'carousel_status')) {
                $table->string('carousel_status', 32)->nullable()->after('carousel_slide_count')
                    ->comment('none|planning|generating|generated|validating|valid|invalid|failed');
            }
        });

        // Indexes
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            $existing = array_column(DB::select('SHOW INDEX FROM social_posts'), 'Key_name');
            $toAdd    = [
                'social_posts_image_validation_status_index' => 'ADD INDEX social_posts_image_validation_status_index (image_validation_status)',
                'social_posts_carousel_status_index'         => 'ADD INDEX social_posts_carousel_status_index (carousel_status)',
                'social_posts_carousel_enabled_index'        => 'ADD INDEX social_posts_carousel_enabled_index (carousel_enabled)',
            ];
            $alters = [];
            foreach ($toAdd as $key => $clause) {
                if (!in_array($key, $existing)) {
                    $alters[] = $clause;
                }
            }
            if ($alters) {
                DB::statement('ALTER TABLE social_posts ' . implode(', ', $alters));
            }
        }
    }

    public function down(): void
    {
        Schema::table('social_posts', function (Blueprint $table) {
            $cols = [
                'image_generation_mode', 'image_prompt_source_hash', 'image_generated_from_caption_hash',
                'image_last_generated_at', 'carousel_enabled', 'carousel_slide_count', 'carousel_status',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('social_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
