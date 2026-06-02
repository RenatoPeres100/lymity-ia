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
    private string $graphBase;        // graph.facebook.com/{version}
    private string $instagramBase;    // graph.instagram.com
    private string $instagramApiBase; // api.instagram.com

    public function __construct()
    {
        $this->graphVersion    = config('meta.graph_version', 'v25.0');
        $this->graphBase       = "https://graph.facebook.com/{$this->graphVersion}";
        $this->instagramBase   = 'https://graph.instagram.com';
        $this->instagramApiBase = 'https://api.instagram.com';
    }

    // ── Config ─────────────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return !empty(config('meta.app_id'))
            && !empty(config('meta.app_secret'))
            && !empty(config('meta.redirect_uri'));
    }

    public function isInstagramBusinessLoginMode(): bool
    {
        return config('meta.auth_mode', 'instagram_business_login') === 'instagram_business_login';
    }

    // ── OAuth ──────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(?User $user = null): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Meta não configurado. Configure META_APP_ID, META_APP_SECRET e META_REDIRECT_URI no .env.');
        }

        $authMode    = config('meta.auth_mode', 'instagram_business_login');
        $state       = Str::random(64);
        $stateHash   = hash('sha256', $state);
        $redirectUri = config('meta.redirect_uri');

        switch ($authMode) {
            case 'instagram_business_login':
                $endpoint = 'https://www.instagram.com/oauth/authorize';
                $scopes   = config('meta.instagram_scopes', ['instagram_business_basic', 'instagram_business_content_publish']);
                break;

            case 'facebook_business_login':
                // Facebook Login uses Facebook-specific scopes — NOT instagram_business_* scopes
                $endpoint = 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth';
                $scopes   = config('meta.facebook_scopes', ['pages_show_list', 'pages_read_engagement', 'instagram_basic', 'instagram_content_publish']);
                Log::warning('[MetaInstagramAuthService] AUTH_MODE=facebook_business_login: usando Facebook OAuth. '
                    . 'Para instagram_business_* scopes, configure META_AUTH_MODE=instagram_business_login.');
                break;

            case 'facebook_login':
            default:
                $endpoint = 'https://www.facebook.com/' . $this->graphVersion . '/dialog/oauth';
                $scopes   = config('meta.facebook_scopes', ['pages_show_list', 'pages_read_engagement', 'instagram_basic', 'instagram_content_publish']);
                Log::warning('[MetaInstagramAuthService] AUTH_MODE legado: ' . $authMode);
                break;
        }

        // Defensive guard: instagram_business_* scopes must NEVER go to facebook.com dialog
        if (str_contains($endpoint, 'facebook.com')) {
            foreach ($scopes as $scope) {
                if (str_starts_with($scope, 'instagram_business_')) {
                    throw new \RuntimeException(
                        'Configuração inválida: escopos instagram_business_* não podem ser enviados para ' . $endpoint . '. '
                        . 'Configure META_AUTH_MODE=instagram_business_login no .env e rode php artisan optimize:clear.'
                    );
                }
            }
        }

        // Defensive guard: instagram_business_login must always use instagram.com
        if ($authMode === 'instagram_business_login' && !str_contains($endpoint, 'instagram.com')) {
            throw new \RuntimeException(
                'Invalid OAuth endpoint for instagram_business_login. Expected Instagram OAuth endpoint (www.instagram.com/oauth/authorize), got: ' . $endpoint
            );
        }

        // Persist state in database
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

        session(['instagram_oauth_state' => $state]);

        $this->logActivity('instagram_oauth_started', $user, [
            'redirect_uri' => $redirectUri,
            'auth_mode'    => $authMode,
            'endpoint'     => $endpoint,
            'scopes'       => implode(',', $scopes),
        ]);

        $params = [
            'client_id'     => config('meta.app_id'),
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(',', $scopes),
            'response_type' => 'code',
            'state'         => $state,
        ];

        // Instagram Business Login optional params to avoid fallback to Facebook Login dialog
        if ($authMode === 'instagram_business_login') {
            $params['enable_fb_login']      = 0;
            $params['force_authentication'] = 1;
        }

        return $endpoint . '?' . http_build_query($params);
    }

    public function handleCallback(array $query, User $user): SocialChannel
    {
        // ── 1. Handle Meta-side errors ────────────────────────────────────────
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
                || stripos($errorDesc, 'blocked') !== false) {
                throw new \RuntimeException(
                    'URL bloqueada pela Meta. Confirme que a Redirect URI '
                    . config('meta.redirect_uri')
                    . ' está cadastrada exatamente em "Valid OAuth Redirect URIs" no app Meta (sem barra final, HTTPS, domínio ia.lymity.com.br).'
                );
            }

            if (stripos($errorCode . $errorDesc, 'invalid_scope') !== false
                || stripos($errorDesc, 'scope') !== false
                || stripos($errorDesc, 'Invalid Scopes') !== false) {
                throw new \RuntimeException(
                    'Escopos recusados pela Meta/Instagram. '
                    . 'Confirme que META_AUTH_MODE=instagram_business_login está no .env '
                    . 'e que as permissões instagram_business_basic e instagram_business_content_publish '
                    . 'estão aprovadas no seu app Meta. Rode: php artisan optimize:clear'
                );
            }

            throw new \RuntimeException("Acesso negado pelo Meta/Instagram: {$errorDesc}");
        }

        // ── 2. Validate code ─────────────────────────────────────────────────
        if (empty($query['code'])) {
            $receivedKeys = implode(', ', array_keys(array_filter($query, fn($v) => !empty($v))));
            $this->logActivity('instagram_oauth_code_missing', $user, [
                'received_params' => $receivedKeys ?: 'nenhum',
                'auth_mode'       => config('meta.auth_mode'),
                'redirect_uri'    => config('meta.redirect_uri'),
            ]);
            throw new \RuntimeException(
                'A Meta/Instagram não retornou o código de autorização. '
                . 'Parâmetros recebidos: [' . ($receivedKeys ?: 'nenhum') . ']. '
                . 'Confirme que a autorização foi concluída e que a Redirect URI '
                . config('meta.redirect_uri')
                . ' está cadastrada exatamente em "Valid OAuth Redirect URIs" no app Meta.'
            );
        }

        // ── 3. Validate state ────────────────────────────────────────────────
        $receivedState = $query['state'] ?? null;
        if (empty($receivedState)) {
            $this->logActivity('instagram_oauth_invalid_state', $user, ['reason' => 'state_missing']);
            $this->ensureChannel()?->update(['last_error' => 'Estado OAuth ausente no callback.']);
            throw new \RuntimeException('Estado OAuth ausente. Tente conectar novamente.');
        }

        $stateHash  = hash('sha256', $receivedState);
        $oauthState = InstagramOAuthState::findValid($stateHash);
        $stateSource = 'database';

        if (!$oauthState) {
            $sessionState = session('instagram_oauth_state');
            if (!empty($sessionState) && hash_equals($sessionState, $receivedState)) {
                $stateSource = 'session_fallback';
            } else {
                $this->logActivity('instagram_oauth_invalid_state', $user, [
                    'reason'     => 'state_not_found',
                    'state_hash' => substr($stateHash, 0, 8) . '...',
                ]);
                $this->ensureChannel()?->update(['last_error' => 'Estado OAuth inválido ou expirado.']);
                throw new \RuntimeException(
                    'Estado OAuth inválido ou expirado. O estado de segurança do login expirou (15 min) '
                    . 'ou a sessão mudou. Tente conectar novamente a partir da tela de Conexão Instagram.'
                );
            }
        }

        if ($oauthState) {
            $oauthState->markUsed();
        }
        session()->forget('instagram_oauth_state');

        // ── 4. Exchange code and get profile (mode-aware) ─────────────────────
        $authMode = config('meta.auth_mode', 'instagram_business_login');

        if ($authMode === 'instagram_business_login') {
            return $this->handleInstagramBusinessLoginCallback($query['code'], $user, $stateSource);
        }

        return $this->handleFacebookLoginCallback($query['code'], $user, $stateSource);
    }

    // ── Instagram Business Login flow ──────────────────────────────────────────

    private function handleInstagramBusinessLoginCallback(string $code, User $user, string $stateSource): SocialChannel
    {
        // Step 1: Exchange code for short-lived token via api.instagram.com
        $tokenData = $this->exchangeInstagramCodeForToken($code);
        $shortToken = $tokenData['access_token'];
        $igUserId   = (string) ($tokenData['user_id'] ?? '');

        // Step 2: Get long-lived token via graph.instagram.com
        $longToken  = $this->getInstagramLongLivedToken($shortToken);

        // Step 3: Get Instagram profile directly
        $profile = $this->getInstagramProfile($longToken['access_token']);

        $expiresAt    = isset($longToken['expires_in'])
            ? now()->addSeconds((int) $longToken['expires_in'])
            : now()->addDays(60);
        $refreshDueAt = $expiresAt->copy()->subDays(15);

        $channel = $this->saveChannelConnection([
            'access_token'        => $longToken['access_token'],
            'token_expires_at'    => $expiresAt,
            'refresh_due_at'      => $refreshDueAt,
            'last_refreshed_at'   => now(),
            'instagram_user_id'   => $profile['id'] ?? $igUserId ?: null,
            'facebook_page_id'    => null,
            'account_name'        => '@' . ($profile['username'] ?? 'lymity.ia'),
            'account_url'         => 'https://instagram.com/' . ($profile['username'] ?? 'lymity.ia'),
            'profile_picture_url' => $profile['profile_picture_url'] ?? null,
            'permissions'         => null,
            'metadata'            => [
                'auth_mode'    => 'instagram_business_login',
                'state_source' => $stateSource,
                'account_type' => $profile['account_type'] ?? null,
            ],
        ], $user);

        $this->logActivity('instagram_oauth_connected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
            'auth_mode'    => 'instagram_business_login',
        ]);

        return $channel;
    }

    // ── Facebook Business Login flow ───────────────────────────────────────────

    private function handleFacebookBusinessLoginCallback(string $code, User $user, string $stateSource): SocialChannel
    {
        $tokenData = $this->exchangeFacebookCodeForToken($code);
        $longToken = $this->getFacebookLongLivedToken($tokenData['access_token']);
        $pages     = $this->getUserPages($longToken['access_token']);

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
            $this->ensureChannel()?->update([
                'last_error' => 'Nenhuma conta Instagram profissional vinculada à Página do Facebook foi encontrada.',
            ]);
            throw new \RuntimeException(
                'Nenhuma conta Instagram profissional vinculada à Página do Facebook foi encontrada. '
                . 'Certifique-se de que sua conta é do tipo Business ou Creator e está vinculada a uma Página do Facebook.'
            );
        }

        $expiresAt    = isset($longToken['expires_in'])
            ? now()->addSeconds((int) $longToken['expires_in'])
            : now()->addDays(60);
        $refreshDueAt = $expiresAt->copy()->subDays(15);

        $channel = $this->saveChannelConnection([
            'access_token'        => $longToken['access_token'],
            'token_expires_at'    => $expiresAt,
            'refresh_due_at'      => $refreshDueAt,
            'last_refreshed_at'   => now(),
            'instagram_user_id'   => $instagramAccount['id'] ?? null,
            'facebook_page_id'    => $pageData['id'] ?? null,
            'account_name'        => '@' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'account_url'         => 'https://instagram.com/' . ($instagramAccount['username'] ?? 'lymity.ia'),
            'profile_picture_url' => $instagramAccount['profile_picture_url'] ?? null,
            'permissions'         => $tokenData['permissions'] ?? null,
            'metadata'            => [
                'page_name'    => $pageData['name'] ?? null,
                'auth_mode'    => 'facebook_business_login',
                'state_source' => $stateSource,
            ],
        ], $user);

        $this->logActivity('instagram_oauth_connected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
            'auth_mode'    => 'facebook_business_login',
        ]);

        return $channel;
    }

    // Alias to keep internal call consistent
    private function handleFacebookLoginCallback(string $code, User $user, string $stateSource): SocialChannel
    {
        return $this->handleFacebookBusinessLoginCallback($code, $user, $stateSource);
    }

    // ── Instagram Business Login — token endpoints ─────────────────────────────

    public function exchangeInstagramCodeForToken(string $code): array
    {
        $response = Http::asForm()->post("{$this->instagramApiBase}/oauth/access_token", [
            'client_id'     => config('meta.app_id'),
            'client_secret' => config('meta.app_secret'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('meta.redirect_uri'),
            'code'          => $code,
        ]);

        $this->assertSuccess($response, 'Falha ao trocar código Instagram por token');

        return $response->json();
    }

    public function getInstagramLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->instagramBase}/access_token", [
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => config('meta.app_secret'),
            'access_token'  => $shortLivedToken,
        ]);

        $this->assertSuccess($response, 'Falha ao obter token de longa duração do Instagram');

        return $response->json();
    }

    public function refreshInstagramToken(string $longLivedToken): array
    {
        $response = Http::get("{$this->instagramBase}/refresh_access_token", [
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $longLivedToken,
        ]);

        $this->assertSuccess($response, 'Falha ao renovar token do Instagram');

        return $response->json();
    }

    public function getInstagramProfile(string $accessToken): array
    {
        $response = Http::get("{$this->instagramBase}/me", [
            'fields'       => 'id,username,name,profile_picture_url,account_type',
            'access_token' => $accessToken,
        ]);

        $this->assertSuccess($response, 'Falha ao buscar perfil do Instagram');

        return $response->json();
    }

    // ── Facebook Business Login — token endpoints ──────────────────────────────

    public function exchangeFacebookCodeForToken(string $code): array
    {
        $response = Http::get("{$this->graphBase}/oauth/access_token", [
            'client_id'     => config('meta.app_id'),
            'client_secret' => config('meta.app_secret'),
            'redirect_uri'  => config('meta.redirect_uri'),
            'code'          => $code,
        ]);

        $this->assertSuccess($response, 'Falha ao trocar código Facebook por token');

        return $response->json();
    }

    public function getFacebookLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->graphBase}/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('meta.app_id'),
            'client_secret'     => config('meta.app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $this->assertSuccess($response, 'Falha ao obter token de longa duração do Facebook');

        return $response->json();
    }

    // Keep old name for backwards-compat in code that calls this directly
    public function exchangeCodeForToken(string $code): array
    {
        return $this->isInstagramBusinessLoginMode()
            ? $this->exchangeInstagramCodeForToken($code)
            : $this->exchangeFacebookCodeForToken($code);
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        return $this->isInstagramBusinessLoginMode()
            ? $this->getInstagramLongLivedToken($shortLivedToken)
            : $this->getFacebookLongLivedToken($shortLivedToken);
    }

    // ── Facebook pages (only used by Facebook Login flow) ──────────────────────

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

        $expiresAt    = $data['token_expires_at'] ?? now()->addDays(60);
        $refreshDueAt = $data['refresh_due_at'] ?? $expiresAt->copy()->subDays(15);

        $channel = SocialChannel::updateOrCreate(
            [
                'company_id' => $company?->id,
                'platform'   => 'instagram',
            ],
            [
                'account_name'        => $data['account_name'] ?? '@lymity.ia',
                'account_url'         => $data['account_url'] ?? 'https://instagram.com/lymity.ia',
                'profile_picture_url' => $data['profile_picture_url'] ?? null,
                'instagram_user_id'   => $data['instagram_user_id'] ?? null,
                'facebook_page_id'    => $data['facebook_page_id'] ?? null,
                'external_account_id' => $data['instagram_user_id'] ?? null,
                'access_token'        => $data['access_token'],
                'token_expires_at'    => $expiresAt,
                'refresh_due_at'      => $refreshDueAt,
                'last_refreshed_at'   => $data['last_refreshed_at'] ?? now(),
                'permissions'         => $data['permissions'] ?? null,
                'metadata'            => $data['metadata'] ?? null,
                'status'              => 'connected',
                'last_error'          => null,
                'last_checked_at'     => now(),
                'disconnected_at'     => null,
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
            if ($this->isInstagramBusinessLoginMode()) {
                // Instagram Business Login: validate via graph.instagram.com/me
                $response = Http::get("{$this->instagramBase}/me", [
                    'fields'       => 'id,username',
                    'access_token' => $channel->access_token,
                ]);

                if ($response->successful() && !empty($response->json('id'))) {
                    $channel->markConnected(['last_checked_at' => now()]);
                } else {
                    $errorMsg = $response->json('error.message') ?? 'Token inválido.';
                    $safe     = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $errorMsg);

                    if ($this->isSessionExpiredError($errorMsg)) {
                        $channel->markNeedsReconnect($safe);
                    } else {
                        $channel->markExpired();
                        $channel->update(['last_error' => $safe]);
                    }
                }
            } else {
                // Facebook Login: validate via debug_token
                $response = Http::get("{$this->graphBase}/debug_token", [
                    'input_token'  => $channel->access_token,
                    'access_token' => config('meta.app_id') . '|' . config('meta.app_secret'),
                ]);

                if ($response->successful()) {
                    $tokenData = $response->json('data', []);

                    if (($tokenData['is_valid'] ?? false) === false) {
                        $errorMsg = $tokenData['error']['message'] ?? 'Token inválido.';
                        $safe     = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $errorMsg);

                        if ($this->isSessionExpiredError($errorMsg)) {
                            $channel->markNeedsReconnect($safe);
                        } else {
                            $channel->markExpired();
                            $channel->update(['last_error' => $safe]);
                        }
                    } else {
                        $channel->markConnected(['last_checked_at' => now()]);
                    }
                } else {
                    $errorBody = $response->json('error.message') ?? $response->body();
                    $safe      = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', (string) $errorBody);

                    if ($this->isSessionExpiredError($errorBody)) {
                        $channel->markNeedsReconnect($safe);
                    } else {
                        $channel->markError('Falha ao verificar token: ' . $safe);
                    }
                }
            }
        } catch (\Throwable $e) {
            $safe = preg_replace('/EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $e->getMessage());
            $channel->markError('Erro ao verificar conexão: ' . $safe);
            Log::warning('[MetaInstagramAuthService] Token check failed: ' . $safe);
        }

        return $channel->fresh();
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

    public function disconnect(SocialChannel $channel, User $user): SocialChannel
    {
        $channel->update([
            'status'          => 'disconnected',
            'access_token'    => null,
            'refresh_token'   => null,
            'refresh_due_at'  => null,
            'last_error'      => null,
            'disconnected_at' => now(),
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
