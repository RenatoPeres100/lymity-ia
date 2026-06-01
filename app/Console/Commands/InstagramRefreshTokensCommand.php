<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramRefreshTokensCommand extends Command
{
    protected $signature   = 'instagram:refresh-tokens';
    protected $description = 'Renova automaticamente tokens Instagram (Meta long-lived) antes do vencimento.';

    private string $graphBase;

    public function handle(): int
    {
        $version         = config('meta.graph_version', 'v25.0');
        $this->graphBase = "https://graph.facebook.com/{$version}";

        $channels = SocialChannel::where('platform', 'instagram')
            ->where('status', 'connected')
            ->whereNotNull('access_token')
            ->get()
            ->filter(fn($ch) => $ch->shouldRefresh());

        if ($channels->isEmpty()) {
            $this->info('Nenhum canal Instagram precisa de renovação agora.');
            return self::SUCCESS;
        }

        $this->info("Canais para renovar: {$channels->count()}");

        $renewed = 0;
        $failed  = 0;

        foreach ($channels as $channel) {
            $this->line("  Renovando canal #{$channel->id} ({$channel->account_name})...");

            try {
                $response = Http::get("{$this->graphBase}/oauth/access_token", [
                    'grant_type'        => 'ig_refresh_token',
                    'access_token'      => $channel->access_token,
                ]);

                // Meta may return fb_exchange_token endpoint for some token types
                if ($response->failed() || empty($response->json('access_token'))) {
                    // Fallback: fb_exchange_token
                    $response = Http::get("{$this->graphBase}/oauth/access_token", [
                        'grant_type'        => 'fb_exchange_token',
                        'client_id'         => config('meta.app_id'),
                        'client_secret'     => config('meta.app_secret'),
                        'fb_exchange_token' => $channel->access_token,
                    ]);
                }

                if ($response->failed() || empty($response->json('access_token'))) {
                    $err = $response->json('error.message') ?? 'Resposta inválida da Meta';
                    $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $err);
                    throw new \RuntimeException($safe);
                }

                $newToken  = $response->json('access_token');
                $expiresIn = $response->json('expires_in');

                $expiresAt    = $expiresIn
                    ? now()->addSeconds((int) $expiresIn)
                    : now()->addDays(60);
                $refreshDueAt = $expiresAt->copy()->subDays(15);

                $channel->update([
                    'access_token'      => $newToken,
                    'token_expires_at'  => $expiresAt,
                    'refresh_due_at'    => $refreshDueAt,
                    'last_refreshed_at' => now(),
                    'last_error'        => null,
                    'last_checked_at'   => now(),
                ]);

                $this->info("  Renovado #{$channel->id} — expira em {$expiresAt->format('d/m/Y')}");
                $renewed++;

                $this->logActivity('instagram_token_refreshed', $channel->id, [
                    'channel_id'   => $channel->id,
                    'account_name' => $channel->account_name,
                    'expires_at'   => $expiresAt->toDateTimeString(),
                ]);

            } catch (\Throwable $e) {
                $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
                $this->error("  FALHOU #{$channel->id}: {$safe}");
                Log::warning("[instagram:refresh-tokens] Canal #{$channel->id} falhou: {$safe}");

                if ($this->isSessionExpiredError($safe)) {
                    $channel->markNeedsReconnect("Renovação falhou: {$safe}");
                    $this->warn("  Canal #{$channel->id} marcado como needs_reconnect.");
                } elseif ($channel->token_expires_at && $channel->token_expires_at->isPast()) {
                    $channel->update([
                        'status'     => 'needs_reconnect',
                        'last_error' => "Renovação falhou e token expirou: {$safe}",
                    ]);
                } else {
                    $channel->update([
                        'last_error' => "Tentativa de renovação falhou: {$safe}",
                    ]);
                }

                $this->logActivity('instagram_token_refresh_failed', $channel->id, [
                    'channel_id'   => $channel->id,
                    'account_name' => $channel->account_name,
                    'error'        => $safe,
                ]);

                $failed++;
            }
        }

        $this->line("RENEWED={$renewed}");
        $this->line("FAILED={$failed}");

        return self::SUCCESS;
    }

    private function isSessionExpiredError(string $message): bool
    {
        $lower = strtolower($message);
        return str_contains($lower, 'session has expired')
            || str_contains($lower, 'token has expired')
            || str_contains($lower, 'access token has expired')
            || str_contains($lower, 'token revoked')
            || str_contains($lower, 'token is invalid')
            || str_contains($lower, 'invalid oauth access token')
            || str_contains($lower, 'error validating access token');
    }

    private function logActivity(string $action, int $channelId, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => null,
                'action'      => $action,
                'module'      => 'instagram',
                'level'       => str_contains($action, 'fail') ? 'warning' : 'info',
                'description' => "Instagram token refresh: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {}
    }
}
