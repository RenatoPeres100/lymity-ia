<?php

namespace App\Services\Instagram;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\InstagramOAuthState;
use App\Models\SocialChannel;
use App\Models\User;
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

    // ── Config ─────────────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return !empty(config('meta.app_id'))
            && !empty(config('meta.app_secret'))
            && !empty(config('meta.redirect_uri'));
    }

    // ── OAuth ──────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(?User $user = null): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Meta não configurado. Configure META_APP_ID, META_APP_SECRET e META_REDIRECT_URI no .env.');
        }

        $authMode    = config('meta.auth_mode', 'facebook_business_login');
        $state       = Str::random(64);
        $stateHash   = hash('sha256', $state);
        $redirectUri = config('meta.redirect_uri');

        // Select scopes and endpoint based on auth_mode
        switch ($authMode) {
            case 'instagram_business_login':
                $endpoint = 'https://www.instagram.com/oauth/authorize';
                $scopes   = config('meta.instagram_scopes', ['instagram_business_basic', 'instagram_business_content_publish']);
                break;

            case 'facebook_login':
                // Legacy mode — still supported but generates a warning
                $endpoint = 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth';
                $scopes   = config('meta.facebook_scopes', ['instagram_business_basic', 'instagram_business_content_publish']);
                Log::warning('[MetaInstagramAuthService] AUTH_MODE=facebook_login (legado). '
                    . 'Este modo pode gerar Invalid Scopes se usar instagram_basic/instagram_content_publish. '
                    . 'Considere migrar para facebook_business_login.');
                break;

            case 'facebook_business_login':
            default:
                $endpoint = 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth';
                $scopes   = config('meta.facebook_scopes', ['instagram_business_basic', 'instagram_business_content_publish']);
                break;
        }

        // Persist state in database — primary validation mechanism
        InstagramOAuthState::create([
            'state_hash'   => $stateHash,
            'user_id'      => $user?->id,
            'provider'     => 'meta',
            'redirect_uri' => $redirectUri,
            'scopes'       => $scopes,
            'auth_mode'    => $authMode,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),
            'expires_at'   => now()->addMinutes(15),
        ]);

        // Session fallback (may fail for cookie/domain/cache reasons)
        session(['instagram_oauth_state' => $state]);

        $this->logActivity('instagram_oauth_started', $user, [
            'redirect_uri' => $redirectUri,
            'auth_mode'    => $authMode,
            'endpoint'     => $endpoint,
            'scopes'       => implode(',', $scopes),
        ]);

        return $endpoint . '?' . http_build_query([
            'client_id'     => config('meta.app_id'),
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(',', $scopes),
            'response_type' => 'code',
            'state'         => $state,
        ]);
    }

    public function handleCallback(array $query, User $user): SocialChannel
    {
        // ── 1. Handle Meta-side errors first ─────────────────────────────────
        if (!empty($query['error'])) {
            $errorCode = $query['error'] ?? '';
            $errorDesc = $query['error_description'] ?? $query['error_reason'] ?? $errorCode;

            $this->logActivity('instagram_oauth_error', $user, [
                'error'       => $errorCode,
                'description' => $errorDesc,
            ]);

            $channel = $this->ensureChannel();
            if ($channel) {
                $channel->update(['last_error' => "Meta error: {$errorCode} — {$errorDesc}"]);
            }

            if (stripos($errorCode . $errorDesc, 'url_blocked') !== false
                || stripos($errorCode . $errorDesc, 'URL blocked') !== false
                || stripos($errorDesc, 'blocked') !== false) {
                throw new \RuntimeException(
                    'URL bloqueada pela Meta. Confirme que a Redirect URI ' .
                    config('meta.redirect_uri') .
                    ' está cadastrada exatamente em "Valid OAuth Redirect URIs" no app Meta (sem barra final, HTTPS, domínio ia.lymity.com.br).'
                );
            }

            if (stripos($errorCode . $errorDesc, 'invalid_scope') !== false
                || stripos($errorDesc, 'scope') !== false
                || stripos($errorDesc, 'Invalid Scopes') !== false) {
                $invalidScopesMsg = 'Escopos inválidos recusados pela Meta. Atualize o .env para usar instagram_business_basic e instagram_business_content_publish.';

                $this->logActivity('instagram_oauth_invalid_scopes', $user, [
                    'error'       => $errorCode,
                    'description' => $errorDesc,
                    'auth_mode'   => config('meta.auth_mode'),
                    'scopes'      => implode(',', config('meta.facebook_scopes', [])),
                ]);

                if ($channel) {
                    $channel->update(['last_error' => $invalidScopesMsg]);
                }

                throw new \RuntimeException(
                    'Escopos recusados pela Meta. O sistema estava pedindo permissões antigas. '
                    . 'Atualize META_FACEBOOK_SCOPES para instagram_business_basic,instagram_business_content_publish '
                    . 'e rode php artisan optimize:clear.'
                );
            }

            throw new \RuntimeException("Acesso negado pelo Meta: {$errorDesc}");
        }

        // ── 2. Validate code ─────────────────────────────────────────────────
        if (empty($query['code'])) {
            throw new \RuntimeException('Código de autorização ausente na resposta da Meta.');
        }

        // ── 3. Validate state ────────────────────────────────────────────────
        $receivedState = $query['state'] ?? null;
        if (empty($receivedState)) {
            $this->logActivity('instagram_oauth_invalid_state', $user, ['reason' => 'state_missing']);
            $channel = $this->ensureChannel();
            $channel?->update(['last_error' => 'Estado OAuth ausente no callback.']);
            throw new \RuntimeException('Estado OAuth ausente. Tente conectar novamente.');
        }

        $stateHash   = hash('sha256', $receivedState);
        $oauthState  = InstagramOAuthState::findValid($stateHash);
        $stateSource = 'database';

        if (!$oauthState) {
            // Fallback: session
            $sessionState = session('instagram_oauth_state');
            if (!empty($sessionState) && hash_equals($sessionState, $receivedState)) {
                $stateSource = 'session_fallback';
            } else {
                $this->logActivity('instagram_oauth_invalid_state', $user, [
                    'reason'     => 'state_not_found',
                    'state_hash' => substr($stateHash, 0, 8) . '...',
                ]);
                $channel = $this->ensureChannel();
                $channel?->update(['last_error' => 'Estado OAuth inválido ou expirado.']);
                throw new \RuntimeException(
                    'Estado OAuth inválido ou expirado. O estado de segurança do login expirou (15 min) ' .
                    'ou a sessão mudou. Tente conectar novamente a partir da tela de Conexão Instagram.'
                );
            }
        }

        // Mark state as used
        if ($oauthState) {
            $oauthState->markUsed();
        }
        session()->forget('instagram_oauth_state');

        // ── 4. Exchange code for tokens ───────────────────────────────────────
        $tokenData = $this->exchangeCodeForToken($query['code']);
        $longToken = $this->getLongLivedToken($tokenData['access_token']);

        // ── 5. Discover Instagram Business Account ────────────────────────────
        $pages = $this->getUserPages($longToken['access_token']);

        $instagramAccount = null;
        $pageData         = null;

        foreach ($pages as $page) {
            try {
                $ig = $this->getInstagramBusinessAccount(
                    $page['id'],
                    $page['access_token'] ?? $longToken['access_token']
                );
                if ($ig) {
                    $instagramAccount = $ig;
                    $pageData         = $page;
                    break;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        if (!$instagramAccount) {
            $channel = $this->ensureChannel();
            $channel?->update([
                'last_error' => 'Nenhuma conta Instagram profissional vinculada à Página do Facebook foi encontrada.',
            ]);
            throw new \RuntimeException(
                'Nenhuma conta Instagram profissional vinculada à Página do Facebook foi encontrada. ' .
                'Certifique-se de que sua conta @lymity.ia é do tipo Business ou Creator e está vinculada a uma Página do Facebook.'
            );
        }

        // ── 6. Save channel ───────────────────────────────────────────────────
        $channel = $this->saveChannelConnection([
            'access_token'      => $longToken['access_token'],
            'token_expires_at'  => isset($longToken['expires_in'])
                ? now()->addSeconds((int) $longToken['expires_in'])
                : now()->addDays(55),
            'instagram_user_id' => $instagramAccount['id'] ?? null,
            'facebook_page_id'  => $pageData['id'] ?? null,
            'account_name'      => '@' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'account_url'       => 'https://instagram.com/' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'permissions'       => $tokenData['permissions'] ?? null,
            'metadata'          => [
                'page_name'    => $pageData['name'] ?? null,
                'auth_mode'    => config('meta.auth_mode', 'facebook_login'),
                'state_source' => $stateSource,
            ],
        ], $user);

        $this->logActivity('instagram_oauth_connected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
        ]);

        return $channel;
    }

    // ── Token exchange ─────────────────────────────────────────────────────────

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::get("{$this->graphBase}/oauth/access_token", [
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
                'account_name'       => $data['account_name'] ?? '@lymity.ia',
                'account_url'        => $data['account_url'] ?? 'https://instagram.com/lymity.ia',
                'instagram_user_id'  => $data['instagram_user_id'] ?? null,
                'facebook_page_id'   => $data['facebook_page_id'] ?? null,
                'external_account_id'=> $data['instagram_user_id'] ?? null,
                'access_token'       => $data['access_token'],
                'token_expires_at'   => $data['token_expires_at'] ?? now()->addDays(55),
                'permissions'        => $data['permissions'] ?? null,
                'metadata'           => $data['metadata'] ?? null,
                'status'             => 'connected',
                'last_error'         => null,
                'last_checked_at'    => now(),
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
        $channel->update([
            'status'       => 'disconnected',
            'access_token' => null,
            'refresh_token'=> null,
            'last_error'   => null,
        ]);

        $this->logActivity('instagram_disconnected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
        ]);

        return $channel->fresh();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function ensureChannel(): ?SocialChannel
    {
        $company = Company::first();
        return SocialChannel::where('platform', 'instagram')
            ->where(function ($q) use ($company) {
                $q->where('company_id', $company?->id)->orWhereNotNull('company_id');
            })
            ->whereNull('client_id')
            ->first();
    }

    private function assertSuccess(\Illuminate\Http\Client\Response $response, string $context): void
    {
        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            $safe  = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', (string) $error);
            Log::error("[MetaInstagramAuthService] {$context}: {$safe}");
            throw new \RuntimeException("{$context}: " . ($response->json('error.message') ?? 'Erro desconhecido'));
        }
    }

    private function logActivity(string $action, ?User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'instagram',
                'level'       => str_contains($action, 'error') || str_contains($action, 'invalid') ? 'error' : 'info',
                'description' => "Instagram OAuth: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {
            // Log failure must never break the OAuth flow
        }
    }
}
