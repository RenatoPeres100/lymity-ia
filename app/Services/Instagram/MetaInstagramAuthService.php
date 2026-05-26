<?php

namespace App\Services\Instagram;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\SocialChannel;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaInstagramAuthService
{
    private string $graphVersion;
    private string $graphBase;

    public function __construct()
    {
        $this->graphVersion = config('meta.graph_version', 'v25.0');
        $this->graphBase    = "https://graph.facebook.com/{$this->graphVersion}";
    }

    // ── Config check ───────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return !empty(config('meta.app_id'))
            && !empty(config('meta.app_secret'))
            && !empty(config('meta.redirect_uri'));
    }

    // ── OAuth ──────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(): string
    {
        abort_unless($this->isConfigured(), 503, 'Meta não configurado. Configure META_APP_ID, META_APP_SECRET e META_REDIRECT_URI.');

        $state  = Str::random(40);
        session(['meta_oauth_state' => $state]);

        $scopes = implode(',', config('meta.scopes', ['pages_show_list', 'instagram_basic', 'instagram_content_publish']));

        return 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth?' . http_build_query([
            'client_id'     => config('meta.app_id'),
            'redirect_uri'  => config('meta.redirect_uri'),
            'scope'         => $scopes,
            'response_type' => 'code',
            'state'         => $state,
        ]);
    }

    public function handleCallback(array $query, User $user): SocialChannel
    {
        // Validate state
        $sessionState = session('meta_oauth_state');
        abort_if(
            empty($query['state']) || $query['state'] !== $sessionState,
            403,
            'Estado OAuth inválido. Tente novamente.'
        );
        session()->forget('meta_oauth_state');

        if (!empty($query['error'])) {
            $this->logActivity('instagram_connection_failed', $user, ['error' => $query['error_description'] ?? $query['error']]);
            abort(422, 'Acesso negado pelo Meta: ' . ($query['error_description'] ?? $query['error']));
        }

        abort_if(empty($query['code']), 422, 'Código de autorização ausente.');

        // Exchange code for token
        $tokenData = $this->exchangeCodeForToken($query['code']);

        // Get long-lived token
        $longToken = $this->getLongLivedToken($tokenData['access_token']);

        // Get pages and Instagram account
        $pages = $this->getUserPages($longToken['access_token']);

        $instagramAccount = null;
        $pageData         = null;

        foreach ($pages as $page) {
            try {
                $ig = $this->getInstagramBusinessAccount($page['id'], $page['access_token'] ?? $longToken['access_token']);
                if ($ig) {
                    $instagramAccount = $ig;
                    $pageData         = $page;
                    break;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $channel = $this->saveChannelConnection([
            'access_token'       => $longToken['access_token'],
            'token_expires_at'   => now()->addSeconds($longToken['expires_in'] ?? 5_184_000),
            'instagram_user_id'  => $instagramAccount['id'] ?? null,
            'facebook_page_id'   => $pageData['id'] ?? null,
            'account_name'       => '@' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'account_url'        => 'https://instagram.com/' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'permissions'        => $tokenData['permissions'] ?? null,
        ], $user);

        $this->logActivity('instagram_connected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
        ]);

        return $channel;
    }

    // ── Token exchange ─────────────────────────────────────────────────────────

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::post("https://graph.facebook.com/{$this->graphVersion}/oauth/access_token", [
            'client_id'     => config('meta.app_id'),
            'client_secret' => config('meta.app_secret'),
            'redirect_uri'  => config('meta.redirect_uri'),
            'code'          => $code,
        ]);

        $this->assertSuccess($response, 'Falha ao trocar código por token');

        return $response->json();
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->graphBase}/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('meta.app_id'),
            'client_secret'     => config('meta.app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $this->assertSuccess($response, 'Falha ao obter token de longa duração');

        return $response->json();
    }

    public function getUserPages(string $accessToken): array
    {
        $response = Http::get("{$this->graphBase}/me/accounts", [
            'access_token' => $accessToken,
            'fields'       => 'id,name,access_token,instagram_business_account',
        ]);

        $this->assertSuccess($response, 'Falha ao buscar páginas do Facebook');

        return $response->json('data', []);
    }

    public function getInstagramBusinessAccount(string $pageId, string $pageAccessToken): ?array
    {
        $response = Http::get("{$this->graphBase}/{$pageId}", [
            'access_token' => $pageAccessToken,
            'fields'       => 'instagram_business_account{id,username,name,profile_picture_url,followers_count}',
        ]);

        $this->assertSuccess($response, 'Falha ao buscar conta Instagram');

        $data = $response->json('instagram_business_account');

        return $data ?: null;
    }

    // ── Channel persistence ────────────────────────────────────────────────────

    public function saveChannelConnection(array $data, User $user): SocialChannel
    {
        $company = Company::first();

        $channel = SocialChannel::updateOrCreate(
            [
                'company_id' => $company?->id,
                'platform'   => 'instagram',
            ],
            [
                'account_name'      => $data['account_name'] ?? '@lymity.ia',
                'account_url'       => $data['account_url'] ?? 'https://instagram.com/lymity.ia',
                'instagram_user_id' => $data['instagram_user_id'] ?? null,
                'facebook_page_id'  => $data['facebook_page_id'] ?? null,
                'access_token'      => $data['access_token'],
                'token_expires_at'  => $data['token_expires_at'] ?? null,
                'permissions'       => $data['permissions'] ?? null,
                'status'            => 'connected',
                'last_error'        => null,
                'last_checked_at'   => now(),
            ]
        );

        return $channel;
    }

    // ── Connection management ──────────────────────────────────────────────────

    public function refreshConnectionStatus(SocialChannel $channel): SocialChannel
    {
        if (!$this->isConfigured()) {
            $channel->markError('Meta não configurado.');
            return $channel;
        }

        if (empty($channel->getRawOriginal('access_token'))) {
            $channel->markDisconnected();
            return $channel;
        }

        try {
            // Verify token is still valid via debug endpoint
            $response = Http::get("{$this->graphBase}/debug_token", [
                'input_token'  => $channel->access_token,
                'access_token' => config('meta.app_id') . '|' . config('meta.app_secret'),
            ]);

            if ($response->successful()) {
                $tokenData = $response->json('data', []);
                if (($tokenData['is_valid'] ?? false) === false) {
                    $channel->markExpired();
                } else {
                    $channel->markConnected(['last_checked_at' => now()]);
                }
            } else {
                $channel->markError('Falha ao verificar token.');
            }
        } catch (\Throwable $e) {
            $channel->markError('Erro ao verificar conexão: ' . $e->getMessage());
            Log::warning('[MetaInstagramAuthService] Token check failed: ' . $e->getMessage());
        }

        return $channel->fresh();
    }

    public function disconnect(SocialChannel $channel, User $user): SocialChannel
    {
        $channel->markDisconnected();

        $this->logActivity('instagram_disconnected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
        ]);

        return $channel->fresh();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function assertSuccess(\Illuminate\Http\Client\Response $response, string $context): void
    {
        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            // Never log the token — strip any token-like strings
            $safe  = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $error);
            Log::error("[MetaInstagramAuthService] {$context}: {$safe}");
            throw new \RuntimeException("{$context}: " . ($response->json('error.message') ?? 'Erro desconhecido'));
        }
    }

    private function logActivity(string $action, User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'    => $user->id,
                'action'     => $action,
                'module'     => 'instagram',
                'level'      => str_contains($action, 'fail') ? 'error' : 'info',
                'description'=> "Instagram: {$action}",
                'metadata'   => $metadata,
            ]);
        } catch (\Throwable) {
            // Log failure should never break the flow
        }
    }
}
