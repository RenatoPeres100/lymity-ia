<?php

namespace App\Console\Commands;

use App\Models\AiImageGeneration;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Console\Command;

class AiImagesGenerateCarousel extends Command
{
    protected $signature = 'ai-images:generate-carousel
        {--channel=social : Channel (blog|social|general)}
        {--title= : Carousel title}
        {--context= : Generation context}
        {--slides=5 : Number of slides (1-10)}';

    protected $description = 'Generate a carousel of AI images via Gemini';

    public function handle(GeminiAiImageService $service): int
    {
        $channel = $this->option('channel');
        $title   = $this->option('title') ?: 'Carrossel ' . now()->format('d/m H:i');
        $context = $this->option('context') ?: '';
        $slides  = min(10, max(1, (int) $this->option('slides')));

        $this->info("Generating carousel: {$slides} slides, channel={$channel}");
        $this->line("Title: {$title}");

        $company = \App\Models\Company::first();

        $generation = AiImageGeneration::create([
            'company_id'      => $company?->id,
            'channel'         => $channel,
            'purpose'         => 'social_carousel',
            'generation_type' => 'carousel',
            'title'           => $title,
            'prompt_context'  => $context,
            'slides_count'    => $slides,
            'aspect_ratio'    => '1:1',
            'status'          => 'draft',
        ]);

        $this->line("Generation ID: {$generation->id}");
        $this->line("Generating {$slides} slides...");

        try {
            $result = $service->generateCarousel($generation);
            $images = $result['images'];
            $errors = $result['errors'] ?? [];

            $this->info("Carousel generated: {$images->count()} slides OK, " . count($errors) . " errors.");
            foreach ($images as $img) {
                $this->line("  Slide {$img->slide_number}: {$img->public_url}");
            }
            if ($errors) {
                foreach ($errors as $err) {
                    $this->warn("  Error: {$err}");
                }
            }
            $this->line("  View at: /admin/ai-images/{$generation->id}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
