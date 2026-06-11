<?php

namespace App\Services\Threads;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\SocialChannel;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ThreadsAuthService
{
    private string $baseUrl;
    private string $graphVersion;

    public function __construct()
    {
        $this->baseUrl      = config('threads.base_url', 'https://graph.threads.net');
        $this->graphVersion = config('threads.graph_version', 'v1.0');
    }

    // ── Configuration ──────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return !empty(config('threads.app_id'))
            && !empty(config('threads.app_secret'))
            && !empty(config('threads.redirect_uri'));
    }

    public function getConfigurationStatus(): array
    {
        $useMetaApp = config('threads.use_meta_app', false);
        $raw        = config('threads._raw', []);

        $appMode = $useMetaApp ? 'meta_app_reused' : 'threads_app';

        // Determine which credentials are present for the active mode
        if ($useMetaApp) {
            $appIdSet     = !empty($raw['meta_app_id_set']);
            $appSecretSet = !empty($raw['meta_secret_set']);
        } else {
            $appIdSet     = !empty($raw['threads_app_id_set']);
            $appSecretSet = !empty($raw['threads_secret_set']);
        }

        $redirectUri    = config('threads.redirect_uri', '');
        $expectedUri    = 'https://ia.lymity.com.br/admin/social/threads/callback';
        $redirectUriSet = !empty($redirectUri);
        $redirectUriOk  = $redirectUri === $expectedUri;

        $scopes              = config('threads.scopes', []);
        $publishingEnabled   = config('threads.publishing_enabled', false);

        $configured = $appIdSet && $appSecretSet && $redirectUriSet;

        $channel = $this->findChannel();

        $missingVars = [];
        if ($useMetaApp) {
            if (empty($raw['meta_app_id_set']))  $missingVars[] = 'META_APP_ID';
            if (empty($raw['meta_secret_set']))  $missingVars[] = 'META_APP_SECRET';
        } else {
            if (empty($raw['threads_app_id_set'])) $missingVars[] = 'THREADS_APP_ID';
            if (empty($raw['threads_secret_set'])) $missingVars[] = 'THREADS_APP_SECRET';
        }
        if (!$redirectUriSet) $missingVars[] = 'THREADS_REDIRECT_URI';

        return [
            'configured'          => $configured,
            'use_meta_app'        => $useMetaApp,
            'app_mode'            => $appMode,
            'app_id_set'          => $appIdSet,
            'app_secret_set'      => $appSecretSet,
            'redirect_uri_set'    => $redirectUriSet,
            'redirect_uri'        => $redirectUri,
            'redirect_uri_ok'     => $redirectUriOk,
            'scopes'              => $scopes,
            'publishing_enabled'  => $publishingEnabled,
            'graph_version'       => $this->graphVersion,
            'base_url'            => $this->baseUrl,
            'oauth_base_url'      => config('threads.oauth_base_url', 'https://threads.net/oauth/authorize'),
            'token_url'           => config('threads.token_url', 'https://graph.threads.net/oauth/access_token'),
            'missing_vars'        => $missingVars,
            // raw flags for diagnostics
            'raw_threads_app_id_set'  => !empty($raw['threads_app_id_set']),
            'raw_threads_secret_set'  => !empty($raw['threads_secret_set']),
            'raw_meta_app_id_set'     => !empty($raw['meta_app_id_set']),
            'raw_meta_secret_set'     => !empty($raw['meta_secret_set']),
            // channel info
            'channel_connected'   => $channel && $channel->isConnected(),
            'channel_status'      => $channel?->status,
            'threads_user_id'     => $channel?->threads_user_id,
            'token_present'       => $channel ? !empty($channel->getRawOriginal('access_token')) : false,
            'token_expires_at'    => $channel?->token_expires_at?->format('d/m/Y H:i'),
            'last_error'          => $channel?->last_error,
            'last_checked_at'     => $channel?->last_checked_at?->format('d/m/Y H:i:s'),
        ];
    }

    // ── OAuth ──────────────────────────────────────────────────────────────────

    public function getAuthorizationUrl(User $user): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException(
                'Threads não configurado. Configure ' .
                (config('threads.use_meta_app') ? 'META_APP_ID, META_APP_SECRET' : 'THREADS_APP_ID, THREADS_APP_SECRET') .
                ' e THREADS_REDIRECT_URI no .env.'
            );
        }

        $state = Str::random(64);
        session(['threads_oauth_state' => $state]);

        $scopes = implode(',', config('threads.scopes', ['threads_basic', 'threads_content_publish']));

        $this->logActivity('threads.connection.started', $user, [
            'app_mode'    => config('threads.use_meta_app') ? 'meta_app_reused' : 'threads_app',
            'redirect_uri' => config('threads.redirect_uri'),
        ]);

        return config('threads.oauth_base_url', 'https://threads.net/oauth/authorize') . '?' . http_build_query([
            'client_id'     => config('threads.app_id'),
            'redirect_uri'  => config('threads.redirect_uri'),
            'scope'         => $scopes,
            'response_type' => 'code',
            'state'         => $state,
        ]);
    }

    public function handleCallback(array $payload, User $user): SocialChannel
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Threads não configurado.');
        }

        if (!empty($payload['error'])) {
            $reason = $payload['error_description'] ?? $payload['error'] ?? 'Erro desconhecido';
            $this->logActivity('threads.connection.failed', $user, ['reason' => $reason]);
            $this->findChannel()?->update(['last_error' => $this->sanitize($reason)]);
            throw new \RuntimeException('Autorização Threads recusada: ' . $reason);
        }

        if (empty($payload['code'])) {
            throw new \RuntimeException('Código OAuth ausente na resposta do Threads.');
        }

        // Validate state
        $receivedState = $payload['state'] ?? null;
        $sessionState  = session('threads_oauth_state');
        if ($receivedState && $sessionState && !hash_equals($sessionState, $receivedState)) {
            $this->logActivity('threads.connection.failed', $user, ['reason' => 'invalid_state']);
            throw new \RuntimeException('Estado OAuth inválido. Tente conectar novamente.');
        }
        session()->forget('threads_oauth_state');

        $tokenData = $this->exchangeCodeForToken($payload['code']);
        $longToken = $this->getLongLivedToken($tokenData['access_token']);
        $profile   = $this->getThreadsProfile($longToken['access_token']);
        $channel   = $this->saveConnection($user, $profile, $longToken);

        $this->logActivity('threads.connection.completed', $user, [
            'channel_id'      => $channel->id,
            'threads_user_id' => $channel->threads_user_id,
            'account_name'    => $channel->account_name,
            'app_mode'        => config('threads.use_meta_app') ? 'meta_app_reused' : 'threads_app',
        ]);

        return $channel;
    }

    // ── Token exchange ─────────────────────────────────────────────────────────

    public function exchangeCodeForToken(string $code): array
    {
        $tokenUrl = config('threads.token_url', 'https://graph.threads.net/oauth/access_token');

        $response = Http::post($tokenUrl, [
            'client_id'     => config('threads.app_id'),
            'client_secret' => config('threads.app_secret'),
            'redirect_uri'  => config('threads.redirect_uri'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            $error = $response->json('error_message') ?? $response->json('error.message') ?? $response->body();
            throw new \RuntimeException('Falha ao obter token Threads: ' . $this->sanitize($error));
        }

        $data = $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Token Threads ausente na resposta da API.');
        }

        return $data;
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->baseUrl}/access_token", [
            'grant_type'    => 'th_exchange_token',
            'client_secret' => config('threads.app_secret'),
            'access_token'  => $shortLivedToken,
        ]);

        if ($response->failed()) {
            Log::warning('[ThreadsAuth] Could not exchange long-lived token — using short-lived: ' . $this->sanitize($response->body()));
            return ['access_token' => $shortLivedToken, 'expires_in' => 3600, 'token_type' => 'bearer'];
        }

        $data = $response->json();
        return [
            'access_token' => $data['access_token'] ?? $shortLivedToken,
            'expires_in'   => $data['expires_in'] ?? (60 * 24 * 60 * 60),
            'token_type'   => $data['token_type'] ?? 'bearer',
        ];
    }

    // ── Profile ────────────────────────────────────────────────────────────────

    public function getThreadsProfile(string $accessToken): array
    {
        $response = Http::get("{$this->baseUrl}/{$this->graphVersion}/me", [
            'fields'       => 'id,username,name,threads_profile_picture_url,threads_biography',
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Falha ao buscar perfil Threads: ' . $this->sanitize($response->json('error.message') ?? $response->body())
            );
        }

        return $response->json();
    }

    // ── Channel persistence ────────────────────────────────────────────────────

    public function saveConnection(User $user, array $profile, array $tokenData): SocialChannel
    {
        $company   = Company::first();
        $companyId = $user->company_id ?? $company?->id;

        $expiresAt = isset($tokenData['expires_in'])
            ? now()->addSeconds((int) $tokenData['expires_in'])
            : now()->addDays(60);

        $data = [
            'company_id'          => $companyId,
            'client_id'           => null,
            'platform'            => 'threads',
            'threads_user_id'     => $profile['id'] ?? null,
            'external_account_id' => $profile['id'] ?? null,
            'instagram_user_id'   => null,
            'facebook_page_id'    => null,
            'account_name'        => $profile['username'] ?? $profile['name'] ?? null,
            'account_url'         => isset($profile['username'])
                ? 'https://www.threads.net/@' . $profile['username']
                : null,
            'profile_picture_url' => $profile['threads_profile_picture_url'] ?? null,
            'access_token'        => $tokenData['access_token'],
            'refresh_token'       => null,
            'token_expires_at'    => $expiresAt,
            'status'              => 'connected',
            'last_error'          => null,
            'last_checked_at'     => now(),
            'permissions'         => config('threads.scopes', []),
            'metadata'            => [
                'app_mode'     => config('threads.use_meta_app') ? 'meta_app_reused' : 'threads_app',
                'connected_by' => $user->id,
                'connected_at' => now()->toISOString(),
                'profile'      => [
                    'id'       => $profile['id'] ?? null,
                    'username' => $profile['username'] ?? null,
                ],
            ],
        ];

        $channel = SocialChannel::updateOrCreate(
            [
                'company_id' => $companyId,
                'platform'   => 'threads',
                'client_id'  => null,
            ],
            $data
        );

        return $channel->fresh();
    }

    // ── Connection management ──────────────────────────────────────────────────

    public function checkConnection(SocialChannel $channel): array
    {
        if (!$channel->hasValidToken()) {
            $channel->markError('Token ausente ou expirado.');
            $this->logActivity('threads.connection.checked', null, [
                'channel_id' => $channel->id,
                'status'     => 'token_invalid',
            ]);
            return ['connected' => false, 'reason' => 'Token inválido ou expirado.'];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->graphVersion}/me", [
                'fields'       => 'id,username',
                'access_token' => $channel->access_token,
            ]);

            if ($response->failed()) {
                $error = $this->sanitize($response->json('error.message') ?? $response->body());
                $channel->markError($error);
                $this->logActivity('threads.connection.checked', null, [
                    'channel_id' => $channel->id,
                    'status'     => 'api_error',
                    'error'      => $error,
                ]);
                return ['connected' => false, 'reason' => $error];
            }

            $profile = $response->json();
            $channel->markConnected([
                'threads_user_id' => $profile['id'] ?? $channel->threads_user_id,
                'account_name'    => $profile['username'] ?? $channel->account_name,
            ]);

            $this->logActivity('threads.connection.checked', null, [
                'channel_id' => $channel->id,
                'status'     => 'ok',
            ]);

            return [
                'connected' => true,
                'profile'   => ['id' => $profile['id'] ?? null, 'username' => $profile['username'] ?? null],
            ];
        } catch (Throwable $e) {
            $safe = $this->sanitize($e->getMessage());
            $channel->markError($safe);
            $this->logActivity('threads.connection.checked', null, [
                'channel_id' => $channel->id,
                'status'     => 'exception',
                'error'      => $safe,
            ]);
            return ['connected' => false, 'reason' => $safe];
        }
    }

    public function disconnect(SocialChannel $channel, ?User $user = null): void
    {
        $channel->update([
            'status'         => 'disconnected',
            'access_token'   => null,
            'refresh_token'  => null,
            'last_error'     => null,
            'disconnected_at'=> now(),
        ]);

        $this->logActivity('threads.connection.disconnected', $user, ['channel_id' => $channel->id]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function findChannel(): ?SocialChannel
    {
        $company = Company::first();
        return SocialChannel::where('platform', 'threads')
            ->where(function ($q) use ($company) {
                $q->where('company_id', $company?->id)->orWhereNotNull('company_id');
            })
            ->whereNull('client_id')
            ->first();
    }

    public function sanitizeError(Throwable|string|array $error): string
    {
        return $this->sanitize($error instanceof Throwable ? $error->getMessage() : (is_array($error) ? json_encode($error) : (string) $error));
    }

    private function sanitize(string $msg): string
    {
        return preg_replace('/THQA[A-Za-z0-9_\-]+|EAA[A-Za-z0-9]+/', '[TOKEN_REDACTED]', $msg);
    }

    private function logActivity(string $action, ?User $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'threads',
                'level'       => str_contains($action, 'fail') || str_contains($action, 'error') ? 'warning' : 'info',
                'description' => "Threads: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (Throwable) {}
    }
}
