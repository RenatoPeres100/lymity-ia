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

class GenerateAiImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries   = 2;

    public function __construct(
        public readonly int $generationId
    ) {}

    public function handle(GeminiAiImageService $service): void
    {
        $generation = AiImageGeneration::find($this->generationId);

        if (!$generation) {
            Log::warning("[GenerateAiImageJob] Generation {$this->generationId} not found.");
            return;
        }

        if ($generation->status === 'completed') {
            return;
        }

        Log::info("[GenerateAiImageJob] Starting single image generation for {$this->generationId}");
        $service->generateSingle($generation);
        Log::info("[GenerateAiImageJob] Completed single image generation for {$this->generationId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[GenerateAiImageJob] Job failed for generation {$this->generationId}: {$exception->getMessage()}");

        $generation = AiImageGeneration::find($this->generationId);
        if ($generation) {
            $generation->update([
                'status'        => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
