<?php

namespace App\Console\Commands;

use App\Models\SocialChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InstagramConnectionCheckCommand extends Command
{
    protected $signature   = 'instagram:connection-check';
    protected $description = 'Valida o canal @lymity.ia: chama /me e a página oficial para confirmar status da conexão.';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== INSTAGRAM CONNECTION CHECK ===');
        $this->info('Canal: @lymity.ia');
        $this->info('');

        $version   = config('meta.graph_version', 'v25.0');
        $graphBase = "https://graph.facebook.com/{$version}";
        $pageId    = config('meta.official.page_id', '1069242536283477');
        $igUserId  = config('meta.official.ig_user_id', '17841434234661171');

        // ── Find channel ──────────────────────────────────────────────────────
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            $this->error('Canal @lymity.ia não encontrado. Configure em /admin/social/instagram.');
            return self::FAILURE;
        }

        $this->line("  ID: {$channel->id}");
        $this->line("  Status atual: {$channel->status}");
        $this->line("  facebook_page_id: " . ($channel->facebook_page_id ?? 'ausente'));
        $this->line("  instagram_user_id: " . ($channel->instagram_user_id ?? 'ausente'));

        if (empty($channel->getRawOriginal('access_token'))) {
            $this->error('Token de acesso ausente. Reconecte em /admin/social/instagram.');
            $channel->markNeedsReconnect('Token ausente detectado por connection-check.');
            return self::FAILURE;
        }

        $token = $channel->access_token;

        // ── Test /me ──────────────────────────────────────────────────────────
        $this->info('');
        $this->info('── Teste /me ────────────────────────────────────────');

        try {
            $meResp = Http::timeout(10)->get("{$graphBase}/me", ['access_token' => $token]);

            if ($meResp->successful() && !empty($meResp->json('id'))) {
                $me = $meResp->json();
                $this->line("  <fg=green>[OK]</> /me → id={$me['id']}, name=" . ($me['name'] ?? '?'));
            } else {
                $err  = $meResp->json('error.message') ?? 'HTTP ' . $meResp->status();
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $err);
                $this->error("  [ERRO] /me falhou: {$safe}");

                if ($this->isExpiredError($err)) {
                    $channel->markNeedsReconnect("Token expirado/revogado: {$safe}");
                    $this->warn("  Canal marcado como needs_reconnect.");
                } else {
                    $channel->markError($safe);
                }

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
            $this->error("  [ERRO] Exceção ao chamar /me: {$safe}");
            $channel->markError($safe);
            return self::FAILURE;
        }

        // ── Test page → instagram_business_account ────────────────────────────
        $this->info('');
        $this->info("── Teste /{$pageId} → instagram_business_account ────");

        try {
            $pageResp = Http::timeout(10)->get("{$graphBase}/{$pageId}", [
                'access_token' => $token,
                'fields'       => 'id,name,instagram_business_account{id,username,name}',
            ]);

            if ($pageResp->successful()) {
                $page   = $pageResp->json();
                $igData = $page['instagram_business_account'] ?? null;

                $this->line("  Página: id={$page['id']}, name=" . ($page['name'] ?? '?'));

                if ($igData) {
                    $this->line("  <fg=green>[OK]</> instagram_business_account: id={$igData['id']}, username=@" . ($igData['username'] ?? '?'));

                    if (($igData['id'] ?? '') !== $igUserId) {
                        $this->warn("  [AVISO] IG User ID esperado: {$igUserId}, obtido: {$igData['id']}");
                    }
                } else {
                    $this->warn("  instagram_business_account não encontrado na página {$pageId}.");
                    $this->warn("  Confirme se a conta @lymity.ia está vinculada a esta página.");
                }
            } else {
                $err  = $pageResp->json('error.message') ?? 'HTTP ' . $pageResp->status();
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $err);
                $this->error("  [ERRO] /{$pageId} falhou: {$safe}");
            }
        } catch (\Throwable $e) {
            $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
            $this->error("  [ERRO] Exceção ao verificar página: {$safe}");
        }

        // ── Update channel status ─────────────────────────────────────────────
        $channel->markConnected(['last_checked_at' => now()]);
        $this->info('');
        $this->line("  <fg=green>[OK]</> Canal marcado como connected. last_checked_at atualizado.");

        $this->info('');
        $this->info('=== FIM DO CONNECTION CHECK ===');
        $this->info('');

        return self::SUCCESS;
    }

    private function isExpiredError(string $message): bool
    {
        $lower = strtolower($message);
        return str_contains($lower, 'session has expired')
            || str_contains($lower, 'token has expired')
            || str_contains($lower, 'access token has expired')
            || str_contains($lower, 'token revoked')
            || str_contains($lower, 'invalid oauth access token')
            || str_contains($lower, 'error validating access token');
    }
}
