<?php

namespace App\Console\Commands;

use App\Models\InstagramOAuthState;
use App\Models\SocialChannel;
use Illuminate\Console\Command;

class InstagramDiagnoseOAuthCommand extends Command
{
    protected $signature   = 'instagram:diagnose-oauth';
    protected $description = 'Diagnóstico do fluxo OAuth Instagram / Meta.';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== INSTAGRAM OAUTH DIAGNOSE ===');
        $this->info('');

        // ── APP CONFIG ────────────────────────────────────────────────────────
        $this->info('── Configuração do App ─────────────────────────────');
        $this->line("  APP_URL             = " . config('app.url'));
        $this->line("  META_AUTH_MODE      = " . config('meta.auth_mode', 'facebook_login'));
        $this->check('META_APP_ID configurado',     !empty(config('meta.app_id')));
        $this->check('META_APP_SECRET configurado', !empty(config('meta.app_secret')));

        $redirectUri  = config('meta.redirect_uri', '');
        $expectedUri  = 'https://ia.lymity.com.br/admin/social/instagram/callback';
        $redirectOk   = $redirectUri === $expectedUri;

        $this->line("  META_REDIRECT_URI   = {$redirectUri}");
        $this->line("  Esperado            = {$expectedUri}");
        $this->check('redirect_uri está correto', $redirectOk);
        if (!$redirectOk) {
            $this->warn("  → Corrija META_REDIRECT_URI no .env e rode: php artisan config:clear");
        }

        $graphVersion = config('meta.graph_version', 'v25.0');
        $this->line("  META_GRAPH_VERSION  = {$graphVersion}");

        $this->info('');

        // ── OAUTH ENDPOINT ────────────────────────────────────────────────────
        $this->info('── OAuth Endpoint (preview sem state real) ─────────');
        $scopes = array_merge(
            config('meta.facebook_scopes', []),
            config('meta.instagram_scopes', [])
        );
        $this->line("  Scopes: " . implode(', ', $scopes));

        if (!empty(config('meta.app_id')) && !empty($redirectUri)) {
            $endpoint = 'https://www.facebook.com/' . $graphVersion . '/dialog/oauth?' . http_build_query([
                'client_id'     => config('meta.app_id'),
                'redirect_uri'  => $redirectUri,
                'scope'         => implode(',', $scopes),
                'response_type' => 'code',
                'state'         => '[STATE_PLACEHOLDER]',
            ]);
            $this->line("  Endpoint: {$endpoint}");
        } else {
            $this->warn("  → Endpoint não pode ser gerado: META_APP_ID ou META_REDIRECT_URI ausentes.");
        }

        $this->info('');

        // ── CHANNEL STATUS ────────────────────────────────────────────────────
        $this->info('── Canal @lymity.ia ────────────────────────────────');

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if ($channel) {
            $this->line("  ID: {$channel->id}");
            $this->line("  Status: {$channel->status}");
            $this->line("  account_name: " . ($channel->account_name ?? 'não definido'));
            $this->line("  instagram_user_id: " . ($channel->instagram_user_id ?? 'ausente'));
            $this->line("  facebook_page_id: " . ($channel->facebook_page_id ?? 'ausente'));
            if ($channel->token_expires_at) {
                $this->line("  token_expires_at: " . $channel->token_expires_at->format('d/m/Y H:i') .
                    ($channel->token_expires_at->isPast() ? ' [EXPIRADO]' : ' [válido]'));
            } else {
                $this->line("  token_expires_at: não definido");
            }
            if ($channel->last_error) {
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $channel->last_error);
                $this->error("  Último erro: {$safe}");
            } else {
                $this->line("  Último erro: nenhum");
            }
        } else {
            $this->warn("  Canal @lymity.ia não encontrado.");
            $this->warn("  → Acesse /admin/social/instagram e clique em Verificar Conexão para criar.");
        }

        $this->info('');

        // ── OAUTH STATES ─────────────────────────────────────────────────────
        $this->info('── Últimos OAuth States (banco) ────────────────────');

        if (!class_exists(InstagramOAuthState::class)) {
            $this->error("  Modelo InstagramOAuthState não encontrado. Rode: php artisan migrate");
        } else {
            try {
                $states = InstagramOAuthState::latest()->limit(5)->get();
                if ($states->isEmpty()) {
                    $this->line("  Nenhum state registrado ainda.");
                } else {
                    foreach ($states as $s) {
                        $expired = $s->expires_at->isPast() ? '[EXPIRADO]' : '[válido]';
                        $used    = $s->used_at ? '[usado em ' . $s->used_at->format('H:i:s') . ']' : '[não usado]';
                        $this->line(sprintf(
                            "  id=%d user_id=%s created=%s expires=%s %s %s",
                            $s->id,
                            $s->user_id ?? 'null',
                            $s->created_at->format('d/m H:i:s'),
                            $s->expires_at->format('d/m H:i:s'),
                            $expired,
                            $used
                        ));
                    }
                }
            } catch (\Throwable $e) {
                $this->error("  Erro ao consultar states: " . $e->getMessage());
                $this->warn("  → Rode: php artisan migrate");
            }
        }

        $this->info('');

        // ── META INSTRUCTIONS ─────────────────────────────────────────────────
        $this->info('── Instruções de Configuração Meta ─────────────────');
        $this->line("  No app Meta Developers configure exatamente:");
        $this->line("  App Domains:              ia.lymity.com.br");
        $this->line("  Valid OAuth Redirect URIs: https://ia.lymity.com.br/admin/social/instagram/callback");
        $this->line("  Client OAuth Login:        ON");
        $this->line("  Web OAuth Login:           ON");
        $this->line("  Use Strict Mode:           ON");
        $this->line("  Enforce HTTPS:             ON");
        $this->info('');

        $this->info('=== FIM DO DIAGNÓSTICO OAUTH ===');
        $this->info('');

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
