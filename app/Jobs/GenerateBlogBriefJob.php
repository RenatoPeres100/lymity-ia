<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Blog\BlogBriefGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBlogBriefJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        public readonly array $data,
        public readonly int   $userId,
    ) {}

    public function handle(BlogBriefGenerationService $service): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error('[GenerateBlogBriefJob] User not found: ' . $this->userId);
            return;
        }

        try {
            $brief = $service->generate($this->data, $user);
            Log::info("[GenerateBlogBriefJob] Brief #{$brief->id} created.");
        } catch (\Throwable $e) {
            Log::error("[GenerateBlogBriefJob] Failed: {$e->getMessage()}");
            ActivityLog::create([
                'user_id'     => $this->userId,
                'action'      => 'generate_brief_failed',
                'module'      => 'blog',
                'level'       => 'error',
                'description' => "Falha ao gerar briefing: {$e->getMessage()}",
            ]);
            throw $e;
        }
    }
}
