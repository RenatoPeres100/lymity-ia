<?php

namespace App\Console\Commands;

use App\Models\AiImageGeneration;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Console\Command;

class AiImagesGenerateSingle extends Command
{
    protected $signature = 'ai-images:generate-single
        {--channel=social : Channel (blog|social|general)}
        {--title= : Image title}
        {--context= : Generation context}
        {--tone= : Visual tone}
        {--style= : Visual style}
        {--overlay=none : Overlay mode (none|safe_top|safe_bottom|safe_left|safe_right|center_free)}';

    protected $description = 'Generate a single AI image via Gemini';

    public function handle(GeminiAiImageService $service): int
    {
        $channel = $this->option('channel');
        $title   = $this->option('title') ?: 'Imagem ' . now()->format('d/m H:i');
        $context = $this->option('context') ?: '';
        $tone    = $this->option('tone') ?: '';
        $style   = $this->option('style') ?: '';
        $overlay = $this->option('overlay') ?: 'none';

        $this->info("Generating single image for channel={$channel}");
        $this->line("Title:   {$title}");

        $company = \App\Models\Company::first();

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'channel'         => $channel,
            'purpose'         => $channel === 'blog' ? 'featured_image' : 'social_single',
            'generation_type' => 'single',
            'title'           => $title,
            'prompt_context'  => $context,
            'tone'            => $tone,
            'visual_style'    => $style,
            'overlay_mode'    => $overlay,
            'slides_count'    => 1,
            'aspect_ratio'    => '1:1',
            'status'          => 'draft',
        ]);

        $this->line("Generation ID: {$generation->id}");

        try {
            $result = $service->generateSingle($generation);
            $image  = $result['image'];

            $this->info('Generated successfully!');
            $this->line("  Public URL: {$image->public_url}");
            $this->line("  Generation: /admin/ai-images/{$generation->id}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
