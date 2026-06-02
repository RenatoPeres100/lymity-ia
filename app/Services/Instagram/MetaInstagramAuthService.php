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

/**
 * Facebook Login + Instagram Graph API flow.
 *
 * Validated flow:
 *   - OAuth endpoint : https://www.facebook.com/{version}/dialog/oauth
 *   - Scopes         : pages_show_list, pages_read_engagement, business_management,
 *                      instagram_basic, instagram_content_publish
 *   - After callback : exchange code → long-lived token → /me/accounts → page instagram_business_account
 *   - PAGE_ID        : 1069242536283477  (Lymity IA)
 *   - IG_USER_ID     : 17841434234661171 (@lymity.ia)
 *   - Publishing     : POST graph.facebook.com/{version}/{ig_user_id}/media
 */
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

        $state       = Str::random(64);
        $stateHash   = hash('sha256', $state);
        $redirectUri = config('meta.redirect_uri');
        $scopes      = config('meta.facebook_scopes', [
            'pages_show_list',
            'pages_read_engagement',
            'business_management',
            'instagram_basic',
            'instagram_content_publish',
        ]);

        // Validated OAuth endpoint: Facebook Login
        $endpoint = "https://www.facebook.com/{$this->graphVersion}/dialog/oauth";

        InstagramOAuthState::create([
            'state_hash'   => $stateHash,
            'user_id'      => $user?->id,
            'provider'     => 'meta',
            'redirect_uri' => $redirectUri,
            'scopes'       => $scopes,
            'auth_mode'    => 'facebook_login',
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),
            'expires_at'   => now()->addMinutes(15),
        ]);

        session(['instagram_oauth_state' => $state]);

        $this->logActivity('instagram_oauth_started', $user, [
            'redirect_uri' => $redirectUri,
            'auth_mode'    => 'facebook_login',
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
                    . ' está cadastrada exatamente em "Valid OAuth Redirect URIs" no app Meta.'
                );
            }

            throw new \RuntimeException("Acesso negado pelo Meta: {$errorDesc}");
        }

        // ── 2. Validate code ─────────────────────────────────────────────────
        if (empty($query['code'])) {
            $receivedKeys = implode(', ', array_keys(array_filter($query, fn($v) => !empty($v))));
            $this->logActivity('instagram_oauth_code_missing', $user, [
                'received_params' => $receivedKeys ?: 'nenhum',
                'redirect_uri'    => config('meta.redirect_uri'),
            ]);
            throw new \RuntimeException(
                'A Meta não retornou o código de autorização. '
                . 'Parâmetros recebidos: [' . ($receivedKeys ?: 'nenhum') . ']. '
                . 'Confirme que a autorização foi concluída e que a Redirect URI está cadastrada no app Meta.'
            );
        }

        // ── 3. Validate state ────────────────────────────────────────────────
        $receivedState = $query['state'] ?? null;
        if (empty($receivedState)) {
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
                $this->logActivity('instagram_oauth_invalid_state', $user, ['reason' => 'state_not_found']);
                $this->ensureChannel()?->update(['last_error' => 'Estado OAuth inválido ou expirado.']);
                throw new \RuntimeException(
                    'Estado OAuth inválido ou expirado (15 min). Tente conectar novamente a partir da tela de Conexão Instagram.'
                );
            }
        }

        if ($oauthState) {
            $oauthState->markUsed();
        }
        session()->forget('instagram_oauth_state');

        // ── 4. Exchange code for short-lived token ────────────────────────────
        $tokenData  = $this->exchangeCodeForToken($query['code']);
        $shortToken = $tokenData['access_token'];

        // ── 5. Exchange for long-lived token ─────────────────────────────────
        $longToken = $this->getLongLivedToken($shortToken);

        // ── 6. Validate /me ───────────────────────────────────────────────────
        $me = $this->getMe($longToken['access_token']);

        // ── 7. Get permissions ────────────────────────────────────────────────
        $permissions = $this->getPermissions($longToken['access_token']);

        // ── 8. Get pages and find Instagram Business Account ──────────────────
        $pages = $this->getUserPages($longToken['access_token']);

        $instagramAccount = null;
        $pageData         = null;

        foreach ($pages as $page) {
            try {
                $ig = $this->getInstagramBusinessAccount(
                    $page['id'],
                    $longToken['access_token']
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
                . 'Certifique-se de que a conta @lymity.ia é do tipo Business ou Creator e está vinculada a uma Página do Facebook.'
            );
        }

        // ── 9. Save channel ───────────────────────────────────────────────────
        $expiresAt    = isset($longToken['expires_in'])
            ? now()->addSeconds((int) $longToken['expires_in'])
            : now()->addDays(55);
        $refreshDueAt = $expiresAt->copy()->subDays(15);

        $channel = $this->saveChannelConnection([
            'access_token'        => $longToken['access_token'],
            'token_expires_at'    => $expiresAt,
            'refresh_due_at'      => $refreshDueAt,
            'last_refreshed_at'   => now(),
            'instagram_user_id'   => $instagramAccount['id'] ?? config('meta.official.ig_user_id'),
            'facebook_page_id'    => $pageData['id'] ?? config('meta.official.page_id'),
            'account_name'        => '@' . ($instagramAccount['username'] ?? config('meta.official.username', 'lymity.ia')),
            'account_url'         => 'https://instagram.com/' . ($instagramAccount['username'] ?? config('meta.official.username', 'lymity.ia')),
            'profile_picture_url' => $instagramAccount['profile_picture_url'] ?? null,
            'permissions'         => $permissions,
            'metadata'            => [
                'auth_mode'       => 'facebook_login',
                'graph_version'   => $this->graphVersion,
                'page_name'       => $pageData['name'] ?? null,
                'ig_name'         => $instagramAccount['name'] ?? null,
                'me_id'           => $me['id'] ?? null,
                'state_source'    => $stateSource,
            ],
        ], $user);

        $this->logActivity('instagram_oauth_connected', $user, [
            'channel_id'   => $channel->id,
            'account_name' => $channel->account_name,
            'auth_mode'    => 'facebook_login',
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

    // ── Graph API helpers ──────────────────────────────────────────────────────

    public function getMe(string $accessToken): array
    {
        $response = Http::get("{$this->graphBase}/me", [
            'access_token' => $accessToken,
            'fields'       => 'id,name',
        ]);

        $this->assertSuccess($response, 'Falha ao validar /me');

        return $response->json();
    }

    public function getPermissions(string $accessToken): array
    {
        try {
            $response = Http::get("{$this->graphBase}/me/permissions", [
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return array_column(
                    array_filter($response->json('data', []), fn($p) => ($p['status'] ?? '') === 'granted'),
                    'permission'
                );
            }
        } catch (\Throwable) {}

        return [];
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

    public function getInstagramBusinessAccount(string $pageId, string $accessToken): ?array
    {
        $response = Http::get("{$this->graphBase}/{$pageId}", [
            'access_token' => $accessToken,
            'fields'       => 'id,name,instagram_business_account{id,username,name,profile_picture_url,followers_count}',
        ]);

        $this->assertSuccess($response, 'Falha ao buscar conta Instagram da página');

        $data = $response->json('instagram_business_account');

        return $data ?: null;
    }

    public function refreshFacebookToken(string $longLivedToken): array
    {
        $response = Http::get("{$this->graphBase}/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('meta.app_id'),
            'client_secret'     => config('meta.app_secret'),
            'fb_exchange_token' => $longLivedToken,
        ]);

        $this->assertSuccess($response, 'Falha ao renovar token Facebook');

        return $response->json();
    }

    // ── Channel persistence ────────────────────────────────────────────────────

    public function saveChannelConnection(array $data, User $user): SocialChannel
    {
        $company = Company::first();

        $expiresAt    = $data['token_expires_at'] ?? now()->addDays(55);
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
        } catch (\Throwable) {}
    }
}
