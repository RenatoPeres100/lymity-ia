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
        private ThreadsAuthService      $auth,
        private ThreadsReadinessService $readiness,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('=== Threads Diagnose ===');
        $this->newLine();

        $status     = $this->auth->getConfigurationStatus();
        $useMetaApp = $status['use_meta_app'];

        $this->info('── Modo de Configuração ──');
        $this->line("  THREADS_USE_META_APP = " . ($useMetaApp ? 'true' : 'false'));
        $this->line("  APP_MODE             = " . $status['app_mode']);
        $this->newLine();

        $this->info('── Credenciais ──');
        if ($useMetaApp) {
            $this->check('META_APP_ID configurado (usado para Threads)',     $status['raw_meta_app_id_set']);
            $this->check('META_APP_SECRET configurado (valor oculto)',       $status['raw_meta_secret_set']);
            if ($status['raw_threads_app_id_set']) {
                $this->line('  <comment>○</comment> THREADS_APP_ID presente (ignorado — usando Meta App)');
            }
            if ($status['raw_meta_app_id_set'] && $status['raw_meta_secret_set']) {
                $this->warn('  ⚠ Confirme que o app Meta possui Threads API habilitado em developers.facebook.com');
            }
        } else {
            $this->check('THREADS_APP_ID configurado',     $status['raw_threads_app_id_set']);
            $this->check('THREADS_APP_SECRET configurado (valor oculto)', $status['raw_threads_secret_set']);
        }
        $this->newLine();

        $this->info('── Configuração .env ──');
        $this->check('THREADS_REDIRECT_URI', $status['redirect_uri_set'], $status['redirect_uri'] ?? '');
        $this->check('THREADS_REDIRECT_URI correto', $status['redirect_uri_ok'],
            $status['redirect_uri_ok'] ? '' : 'Esperado: https://ia.lymity.com.br/admin/social/threads/callback');
        $this->line('  THREADS_SCOPES:            ' . implode(', ', $status['scopes']));
        $this->line('  THREADS_GRAPH_VERSION:     ' . $status['graph_version']);
        $this->line('  THREADS_BASE_URL:          ' . $status['base_url']);
        $this->line('  THREADS_OAUTH_BASE_URL:    ' . $status['oauth_base_url']);
        $this->line('  THREADS_TOKEN_URL:         ' . $status['token_url']);
        $this->check('THREADS_PUBLISHING_ENABLED', $status['publishing_enabled']);
        $this->newLine();

        if (!$status['configured']) {
            $this->warn('  ⚠ Threads não configurado. Variáveis ausentes: ' . implode(', ', $status['missing_vars']));
            if ($useMetaApp) {
                $this->line('  Opção A (Meta App reused) — adicione ao .env:');
                $this->line('      THREADS_USE_META_APP=true');
                $this->line('      THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback');
                $this->line('      # META_APP_ID e META_APP_SECRET devem estar definidos');
            } else {
                $this->line('  Opção B (App dedicado) — adicione ao .env:');
                $this->line('      THREADS_USE_META_APP=false');
                $this->line('      THREADS_APP_ID=<seu_threads_app_id>');
                $this->line('      THREADS_APP_SECRET=<seu_threads_app_secret>');
                $this->line('      THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback');
            }
            $this->line('  Em seguida rode: php artisan config:clear && php artisan optimize:clear');
            $this->line('  Acesse: /admin/social/threads para conectar o canal.');
            $this->newLine();
        }

        $this->info('── Canal Threads ──');
        $channel = SocialChannel::where('platform', 'threads')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            $this->warn('  Canal Threads não encontrado. Conecte em /admin/social/threads.');
        } else {
            $this->check('Canal encontrado',          true,                                "ID {$channel->id}");
            $this->check('Status connected',          $channel->status === 'connected',    $channel->status);
            $this->check('Token presente',            !empty($channel->getRawOriginal('access_token')));
            $this->check('Token não expirado',        !$channel->isExpired(),              $channel->token_expires_at?->format('d/m/Y H:i'));
            $this->check('threads_user_id presente',  !empty($channel->threads_user_id),  $channel->threads_user_id ?? 'null');
            if ($channel->permissions) {
                $this->line('  Permissions: ' . implode(', ', (array) $channel->permissions));
            }
            if ($channel->last_error) {
                $this->warn('  Último erro: ' . $channel->last_error);
            }
            if ($channel->last_checked_at) {
                $this->line('  Última verificação: ' . $channel->last_checked_at->format('d/m/Y H:i:s'));
            }
            if (isset($channel->metadata['app_mode'])) {
                $this->line('  App mode na conexão: ' . $channel->metadata['app_mode']);
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
            $this->check('platform=threads',          $post->platform === 'threads',                 $post->platform);
            $this->check('content_type=threads_text', $post->content_type === 'threads_text',        $post->content_type);
            $this->check('status aprovado/agendado',  in_array($post->status, ['approved','scheduled']), $post->status);
            $this->check('Texto presente',            !empty(trim($post->main_caption ?? '')));
            $this->check('approved_by preenchido',    !empty($post->approved_by),                    $post->approved_by ? 'user #' . $post->approved_by : 'null');

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
        $icon   = $ok ? '<info>✓</info>' : ($ok === null ? '<comment>○</comment>' : '<error>✗</error>');
        $suffix = $detail ? "  ({$detail})" : '';
        $this->line("  {$icon} {$label}{$suffix}");
    }
}
