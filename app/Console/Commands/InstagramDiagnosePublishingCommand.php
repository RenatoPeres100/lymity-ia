<?php

namespace App\Console\Commands;

use App\Models\SocialChannel;
use App\Models\SocialPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstagramDiagnosePublishingCommand extends Command
{
    protected $signature   = 'instagram:diagnose-publishing';
    protected $description = 'Diagnóstico completo do pipeline de publicação Instagram @lymity.ia.';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== INSTAGRAM PUBLISHING DIAGNOSE ===');
        $this->info('Canal oficial: @lymity.ia');
        $this->info('');

        $ok = true;

        // ── META CONFIG ───────────────────────────────────────────────────────
        $this->info('── Configuração Meta ───────────────────────────────');
        $ok &= $this->check('META_APP_ID configurado',      !empty(config('meta.app_id')));
        $ok &= $this->check('META_APP_SECRET configurado',  !empty(config('meta.app_secret')));
        $ok &= $this->check('META_GRAPH_VERSION configurado', !empty(config('meta.graph_version')));

        $redirectUri = config('meta.redirect_uri');
        $this->line("  META_REDIRECT_URI={$redirectUri}");
        $ok &= $this->check(
            'META_REDIRECT_URI correto',
            $redirectUri === 'https://ia.lymity.com.br/admin/social/instagram/callback'
        );

        // Validated flow: facebook_login + Instagram Graph API
        $authMode = config('meta.auth_mode', 'facebook_login');
        $this->line("  META_AUTH_MODE={$authMode}");

        if ($authMode === 'facebook_login') {
            $this->line("  <fg=green>[OK]</> Fluxo validado: facebook_login + Instagram Graph API via Facebook Pages");
            $this->line("  Endpoint: https://www.facebook.com/v25.0/dialog/oauth");
        } elseif ($authMode === 'instagram_business_login') {
            $this->error("  [ERROR] instagram_business_login não é o fluxo validado neste projeto.");
            $this->error("  Configure META_AUTH_MODE=facebook_login no .env e rode php artisan optimize:clear");
            $ok = false;
        } else {
            $this->warn("  [WARN] auth_mode={$authMode} — esperado: facebook_login");
        }

        // Scope audit — validated scopes for facebook_login
        $validatedScopes = ['pages_show_list', 'pages_read_engagement', 'business_management', 'instagram_basic', 'instagram_content_publish'];
        $fbScopes        = config('meta.facebook_scopes', []);
        $this->line("  META_FACEBOOK_SCOPES=" . implode(', ', $fbScopes));

        $missingScopes = array_diff($validatedScopes, $fbScopes);
        if (empty($missingScopes)) {
            $this->line("  <fg=green>[OK]</> Scopes validados configurados: " . implode(', ', $fbScopes));
        } else {
            $this->error("  [ERROR] Scopes ausentes: " . implode(', ', $missingScopes));
            $this->error("  Configure: META_FACEBOOK_SCOPES=pages_show_list,pages_read_engagement,business_management,instagram_basic,instagram_content_publish");
        }

        // Warn if instagram_business_* scopes are mixed in
        $wrongMix = array_intersect($fbScopes, ['instagram_business_basic', 'instagram_business_content_publish']);
        if (!empty($wrongMix)) {
            $this->error("  [ERROR] instagram_business_* scopes não devem estar em META_FACEBOOK_SCOPES para facebook_login: " . implode(', ', $wrongMix));
        }

        $graphVersion = config('meta.graph_version', 'v25.0');
        $this->line("  META_GRAPH_VERSION={$graphVersion}");

        $publishingEnabled = config('meta.instagram_publishing_enabled', false);
        $this->line("  INSTAGRAM_PUBLISHING_ENABLED=" . ($publishingEnabled ? 'true' : 'false'));

        if (!$publishingEnabled) {
            $this->warn("  → Configure INSTAGRAM_PUBLISHING_ENABLED=true no .env para habilitar publicação.");
        }

        $this->info('');

        // ── FEATURES ─────────────────────────────────────────────────────────
        $this->info('── Feature Flags ───────────────────────────────────');
        $this->check('social_media_module=true',   config('features.social_media_module', false));
        $this->check('instagram_pipeline=true',    config('features.instagram_pipeline', false));
        $this->check('instagram_connection=true',  config('features.instagram_connection', false));
        $this->check('instagram_publishing=true',  config('features.instagram_publishing', false));
        $this->check('publishing_queue=true',      config('features.publishing_queue', false));
        $this->info('');

        // ── CANAL @lymity.ia ─────────────────────────────────────────────────
        $this->info('── Canal @lymity.ia ────────────────────────────────');

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$this->check('Canal @lymity.ia existe', $channel !== null)) {
            $this->warn("  → Crie o canal em /admin/social/instagram e conecte.");
            $ok = false;
        } else {
            $this->line("  Canal ID: {$channel->id}");
            $this->line("  Status: {$channel->status}");
            $this->line("  account_name: " . ($channel->account_name ?? 'não definido'));
            $this->check('Canal status=connected', $channel->status === 'connected');
            $this->check('instagram_user_id preenchido', !empty($channel->instagram_user_id));
            $this->check('facebook_page_id preenchido', !empty($channel->facebook_page_id));

            $this->line("  instagram_user_id: " . ($channel->instagram_user_id ?? 'ausente'));
            $this->line("  facebook_page_id: " . ($channel->facebook_page_id ?? 'ausente'));

            if ($channel->token_expires_at) {
                $expired     = $channel->token_expires_at->isPast();
                $expiresLabel = $channel->token_expires_at->format('d/m/Y H:i');
                $this->check("Token válido (expira {$expiresLabel})", !$expired);
                if ($expired) {
                    $this->warn("  → Token expirado. Reconecte em /admin/social/instagram.");
                }
            } else {
                $this->warn("  token_expires_at: não definido.");
            }

            if ($channel->permissions) {
                $this->line("  Permissões: " . (is_array($channel->permissions) ? implode(', ', $channel->permissions) : $channel->permissions));
            }

            if ($channel->last_checked_at) {
                $this->line("  Último check: " . $channel->last_checked_at->format('d/m/Y H:i'));
            }

            if ($channel->last_error) {
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $channel->last_error);
                $this->error("  Último erro: {$safe}");
            }
        }

        $this->info('');

        // ── COMMANDS ─────────────────────────────────────────────────────────
        $this->info('── Commands ────────────────────────────────────────');

        $commands = Artisan::all();
        $this->check('social:publish-due registrado',           isset($commands['social:publish-due']));
        $this->check('content:run-publishing-cycle registrado', isset($commands['content:run-publishing-cycle']));
        $this->check('blog:publish-due registrado',             isset($commands['blog:publish-due']));
        $this->info('');

        // ── SCHEDULER ────────────────────────────────────────────────────────
        $this->info('── Scheduler / Cron ────────────────────────────────');

        $whoami  = trim((string) shell_exec('whoami 2>/dev/null'));
        $this->line("  Usuário verificado: {$whoami}");

        $crontab = shell_exec('crontab -l 2>/dev/null') ?? '';

        // Accept common variations of schedule:run
        $cronPatterns = [
            'schedule:run',
        ];
        $cronFound    = false;
        $cronLine     = null;

        foreach (explode("\n", $crontab) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            foreach ($cronPatterns as $pattern) {
                if (str_contains($trimmed, $pattern)) {
                    $cronFound = true;
                    $cronLine  = $trimmed;
                    break 2;
                }
            }
        }

        if ($cronFound) {
            $this->check('Cron schedule:run detectado', true);
            // Show line but redact any potential secrets
            $safeLine = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $cronLine ?? '');
            $this->line("  Linha: {$safeLine}");
        } else {
            $this->check('Cron schedule:run não detectado', false);
            $this->warn("  → Adicione ao crontab do usuário {$whoami}:");
            $this->warn("  * * * * * cd /var/www/lymity-ia && php artisan schedule:run >> /dev/null 2>&1");
        }

        $this->info('');

        // ── SOCIAL POSTS ─────────────────────────────────────────────────────
        $this->info('── Posts Sociais ───────────────────────────────────');

        $scheduledCount = SocialPost::whereNull('client_id')
            ->where('status', 'scheduled')
            ->count();
        $this->line("  Posts scheduled (agência): {$scheduledCount}");

        $dueCount = SocialPost::whereNull('client_id')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->count();
        $this->line("  Posts due agora: {$dueCount}");

        $withImageCount = SocialPost::whereNull('client_id')
            ->whereNotNull('public_image_url')
            ->where('public_image_url', 'like', 'https://%')
            ->count();
        $this->line("  Posts com public_image_url válida: {$withImageCount}");

        $recentErrors = SocialPost::whereNull('client_id')
            ->where('status', 'failed')
            ->whereNotNull('publication_error')
            ->latest('updated_at')
            ->limit(3)
            ->get();

        if ($recentErrors->isNotEmpty()) {
            $this->warn("  Últimos erros de publicação:");
            foreach ($recentErrors as $p) {
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $p->publication_error ?? '');
                $this->warn("    #{$p->id} ({$p->title}): {$safe}");
            }
        }

        $this->info('');
        $this->info('=== FIM DO DIAGNÓSTICO ===');
        $this->info('');

        if (!$publishingEnabled) {
            $this->warn('[PUBLISHING DESABILITADO] INSTAGRAM_PUBLISHING_ENABLED=false');
            $this->warn('Após conectar e validar o canal, defina INSTAGRAM_PUBLISHING_ENABLED=true no .env e rode:');
            $this->warn('  php artisan config:clear && php artisan optimize:clear');
        }

        return self::SUCCESS;
    }

    private function check(string $label, bool $result): bool
    {
        if ($result) {
            $this->line("  <fg=green>[OK]</> {$label}");
        } else {
            $this->line("  <fg=red>[ERROR]</> {$label}");
        }
        return $result;
    }
}
