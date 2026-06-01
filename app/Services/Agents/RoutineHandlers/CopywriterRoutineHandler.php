<?php

namespace App\Services\Agents\RoutineHandlers;

use App\Models\ActivityLog;
use App\Models\AgentRoutine;
use App\Models\AgentRoutineRun;
use App\Models\BlogPost;
use App\Models\SocialPost;
use App\Services\AI\AIProviderManager;
use Illuminate\Support\Facades\Log;
use Throwable;

class CopywriterRoutineHandler
{
    public function __construct(private AIProviderManager $providerManager) {}

    /**
     * Reviews pending content and adds suggestions.
     * Does NOT publish or approve — only adds revision_notes / metadata suggestions.
     */
    public function handle(AgentRoutine $routine, AgentRoutineRun $run, int $quantity, bool $dryRun = false): array
    {
        $summaries    = [];
        $itemsCreated = 0;

        // Find pending blog posts
        $blogs = BlogPost::whereIn('status', ['pending_approval', 'draft'])
            ->whereNull('client_id')
            ->whereNull('revision_notes')
            ->latest()->limit($quantity)->get();

        foreach ($blogs as $post) {
            try {
                if ($dryRun) {
                    $summaries[] = "[dry-run] BlogPost #{$post->id} seria revisado.";
                    continue;
                }

                $suggestion = $this->reviewBlogPost($post, $routine);
                $post->update(['revision_notes' => $suggestion]);
                $itemsCreated++;
                $run->increment('items_created');
                $summaries[] = "BlogPost #{$post->id} revisado.";

                $this->logActivity('agent_routine_copy_reviewed', $routine, $run, [
                    'model' => 'BlogPost', 'post_id' => $post->id,
                ]);
            } catch (Throwable $e) {
                Log::warning("[CopywriterRoutineHandler] Blog #{$post->id}: " . $e->getMessage());
                $summaries[] = "Erro blog #{$post->id}: " . $e->getMessage();
            }
        }

        // If few blog posts, also review social posts
        $remaining = $quantity - $itemsCreated;
        if ($remaining > 0) {
            $socials = SocialPost::whereIn('status', ['pending_approval', 'draft'])
                ->whereNull('client_id')
                ->latest()->limit($remaining)->get();

            foreach ($socials as $post) {
                if ($dryRun) {
                    $summaries[] = "[dry-run] SocialPost #{$post->id} seria revisado.";
                    continue;
                }

                try {
                    $suggestion = $this->reviewSocialPost($post, $routine);
                    $meta = (array) ($post->metadata ?? []);
                    $meta['copywriter_suggestion'] = $suggestion;
                    $post->update(['metadata' => $meta]);
                    $itemsCreated++;
                    $run->increment('items_created');
                    $summaries[] = "SocialPost #{$post->id} revisado.";
                } catch (Throwable $e) {
                    $summaries[] = "Erro social #{$post->id}: " . $e->getMessage();
                }
            }
        }

        return ['itemsCreated' => $itemsCreated, 'approvalsCreated' => 0, 'summaries' => $summaries];
    }

    private function reviewBlogPost(BlogPost $post, AgentRoutine $routine): string
    {
        $provider = $this->providerManager->provider();

        $response = $provider->generateText([
            'system_prompt' => 'Você é o Copywriter IA da Lymity. Revise este post de blog e sugira melhorias sem aprovar nem publicar. Seja direto e útil.',
            'user_message'  => "Revise este post:\nTítulo: {$post->title}\n\nConteúdo:\n" . strip_tags(mb_substr($post->content ?? '', 0, 1500)),
            'json_mode'     => false,
            'max_tokens'    => 600,
            'temperature'   => 0.5,
        ]);

        if (!$response->success) {
            throw new \RuntimeException("Gemini falhou: {$response->error_message}");
        }

        return mb_substr($response->text, 0, 2000);
    }

    private function reviewSocialPost(SocialPost $post, AgentRoutine $routine): string
    {
        $provider = $this->providerManager->provider();

        $response = $provider->generateText([
            'system_prompt' => 'Você é o Copywriter IA da Lymity. Revise esta legenda de Instagram e sugira melhorias sem aprovar nem publicar.',
            'user_message'  => "Revise esta legenda:\n{$post->main_caption}",
            'json_mode'     => false,
            'max_tokens'    => 400,
            'temperature'   => 0.5,
        ]);

        return $response->success ? mb_substr($response->text, 0, 1000) : 'Revisão indisponível.';
    }

    private function logActivity(string $action, AgentRoutine $routine, AgentRoutineRun $run, array $extra = []): void
    {
        try {
            ActivityLog::create([
                'user_id' => null, 'action' => $action, 'module' => 'agent_routines', 'level' => 'info',
                'description' => "CopywriterRoutineHandler: {$action}",
                'metadata' => array_merge(['routine_id' => $routine->id, 'run_id' => $run->id], $extra),
            ]);
        } catch (Throwable) {}
    }
}
