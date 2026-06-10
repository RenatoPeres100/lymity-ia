<?php

namespace App\Console\Commands\Threads;

use App\Models\SocialChannel;
use App\Models\SocialPost;
use App\Services\Threads\ThreadsAuthService;
use App\Services\Threads\ThreadsReadinessService;
use Illuminate\Console\Command;

class DiagnoseThreadsCommand extends Command
{
    protected $signature   = 'threads:diagnose {--post=}';
    protected $description = 'Diagnose Threads configuration, connection, and publishing readiness.';

    public function __construct(
        private ThreadsAuthService     $auth,
        private ThreadsReadinessService $readiness,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('=== Threads Diagnose ===');
        $this->newLine();

        $status = $this->auth->getConfigurationStatus();

        $this->info('── Configuração .env ──');
        $this->check('THREADS_APP_ID',             $status['app_id_set']);
        $this->check('THREADS_APP_SECRET',         $status['app_secret_set']);
        $this->check('THREADS_REDIRECT_URI',       $status['redirect_uri_set']);
        $this->line('  THREADS_SCOPES: ' . implode(', ', $status['scopes']));
        $this->check('THREADS_PUBLISHING_ENABLED', $status['publishing_enabled']);
        $this->newLine();

        $this->info('── Canal Threads ──');
        $channel = SocialChannel::where('platform', 'threads')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            $this->warn('  Canal Threads não encontrado. Conecte em /admin/social/threads.');
        } else {
            $this->check('Canal encontrado',           true, "ID {$channel->id}");
            $this->check('Status connected',           $channel->status === 'connected', $channel->status);
            $this->check('Token presente',             !empty($channel->getRawOriginal('access_token')));
            $this->check('Token não expirado',         !$channel->isExpired(), $channel->token_expires_at?->format('d/m/Y H:i'));
            $this->check('threads_user_id presente',   !empty($channel->threads_user_id), $channel->threads_user_id ?? 'null');
            if ($channel->permissions) {
                $this->line('  Permissions: ' . implode(', ', (array)$channel->permissions));
            }
            if ($channel->last_error) {
                $this->warn('  Último erro: ' . $channel->last_error);
            }
        }
        $this->newLine();

        $this->info('── Posts por status ──');
        $statuses = ['draft', 'pending_approval', 'approved', 'scheduled', 'publishing', 'published', 'failed', 'rejected'];
        foreach ($statuses as $s) {
            $count = SocialPost::where('platform', 'threads')->where('status', $s)->count();
            if ($count > 0) {
                $this->line("  {$s}: {$count}");
            }
        }
        $this->newLine();

        $this->info('── Últimos erros ──');
        $failed = SocialPost::where('platform', 'threads')
            ->where('status', 'failed')
            ->whereNotNull('publication_error')
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'publication_error', 'updated_at']);
        if ($failed->isEmpty()) {
            $this->line('  Nenhum post com erro.');
        } else {
            foreach ($failed as $f) {
                $this->warn("  #{$f->id} [{$f->updated_at->format('d/m')}]: " . mb_substr($f->publication_error, 0, 80));
            }
        }
        $this->newLine();

        // Post-specific diagnosis
        $postId = $this->option('post');
        if ($postId) {
            $this->info("── Post #{$postId} ──");
            $post = SocialPost::find($postId);
            if (!$post) {
                $this->error("  Post #{$postId} não encontrado.");
                return self::FAILURE;
            }
            $this->check('platform=threads',      $post->platform === 'threads', $post->platform);
            $this->check('content_type=threads_text', $post->content_type === 'threads_text', $post->content_type);
            $this->check('status', in_array($post->status, ['approved','scheduled']), $post->status);
            $this->check('Texto presente',        !empty(trim($post->main_caption ?? '')));
            $this->check('approved_by preenchido',!empty($post->approved_by), $post->approved_by ? 'user #'.$post->approved_by : 'null');
            $this->check('social_channel_id',     $post->social_channel_id ? true : null, $post->social_channel_id ? "#{$post->social_channel_id}" : 'null (usará canal default)');

            $canPub = $this->readiness->canBePublished($post);
            $this->check('Pode publicar', $canPub['ready']);
            if (!empty($canPub['reasons'])) {
                foreach ($canPub['reasons'] as $r) {
                    $this->warn("    Bloqueio: {$r}");
                }
            }
        }

        return self::SUCCESS;
    }

    private function check(string $label, mixed $ok, string $detail = ''): void
    {
        $icon = $ok ? '<info>✓</info>' : ($ok === null ? '<comment>○</comment>' : '<error>✗</error>');
        $suffix = $detail ? "  ({$detail})" : '';
        $this->line("  {$icon} {$label}{$suffix}");
    }
}
