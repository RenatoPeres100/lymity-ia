<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\ContentBrief;
use App\Models\User;
use App\Services\Blog\BlogDraftGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBlogDraftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(
        public readonly int $briefId,
        public readonly int $userId,
    ) {}

    public function handle(BlogDraftGenerationService $service): void
    {
        $brief = ContentBrief::find($this->briefId);
        $user  = User::find($this->userId);

        if (!$brief || !$user) {
            Log::error("[GenerateBlogDraftJob] Brief or user not found. Brief: {$this->briefId}, User: {$this->userId}");
            return;
        }

        try {
            $post = $service->generateFromBrief($brief, $user);
            Log::info("[GenerateBlogDraftJob] Post #{$post->id} created from brief #{$brief->id}.");
        } catch (\Throwable $e) {
            Log::error("[GenerateBlogDraftJob] Failed: {$e->getMessage()}");
            ActivityLog::create([
                'user_id'      => $this->userId,
                'action'       => 'generate_draft_failed',
                'module'       => 'blog',
                'level'        => 'error',
                'description'  => "Falha ao gerar artigo do briefing #{$this->briefId}: {$e->getMessage()}",
                'subject_type' => ContentBrief::class,
                'subject_id'   => $this->briefId,
            ]);
            throw $e;
        }
    }
}
