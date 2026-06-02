<?php

namespace App\Jobs;

use App\Models\AiImageGeneration;
use App\Services\AiImages\GeminiAiImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiCarouselJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    public function __construct(
        public readonly int $generationId
    ) {}

    public function handle(GeminiAiImageService $service): void
    {
        $generation = AiImageGeneration::find($this->generationId);

        if (!$generation) {
            Log::warning("[GenerateAiCarouselJob] Generation {$this->generationId} not found.");
            return;
        }

        if ($generation->status === 'completed') {
            return;
        }

        Log::info("[GenerateAiCarouselJob] Starting carousel generation for {$this->generationId} ({$generation->slides_count} slides)");
        $service->generateCarousel($generation);
        Log::info("[GenerateAiCarouselJob] Completed carousel generation for {$this->generationId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[GenerateAiCarouselJob] Job failed for generation {$this->generationId}: {$exception->getMessage()}");

        $generation = AiImageGeneration::find($this->generationId);
        if ($generation) {
            $generation->update([
                'status'        => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
