<?php

namespace App\Services\Threads;

use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    public function isConfigured(): bool
    {
        return !empty(config('threads.app_id'))
            && !empty(config('threads.app_secret'))
            && !empty(config('threads.redirect_uri'));
    }

    public function getConfigurationStatus(): array
    {
        return [
            'configured'          => $this->isConfigured(),
            'app_id_set'          => !empty(config('threads.app_id')),
            'app_secret_set'      => !empty(config('threads.app_secret')),
            'redirect_uri_set'    => !empty(config('threads.redirect_uri')),
            'redirect_uri'        => config('threads.redirect_uri'),
            'scopes'              => config('threads.scopes', []),
            'publishing_enabled'  => config('threads.publishing_enabled', false),
            'graph_version'       => $this->graphVersion,
            'base_url'            => $this->baseUrl,
        ];
    }

    public function getAuthorizationUrl(User $user): string
    {
        abort_unless($this->isConfigured(), 422, 'Threads não configurado. Defina THREADS_APP_ID, THREADS_APP_SECRET e THREADS_REDIRECT_URI no .env.');

        $state  = base64_encode(json_encode(['user_id' => $user->id, 'ts' => now()->timestamp]));
        $scopes = implode(',', config('threads.scopes', ['threads_basic', 'threads_content_publish']));

        return 'https://threads.net/oauth/authorize?' . http_build_query([
            'client_id'     => config('threads.app_id'),
            'redirect_uri'  => config('threads.redirect_uri'),
            'scope'         => $scopes,
            'response_type' => 'code',
            'state'         => $state,
        ]);
    }

    public function handleCallback(array $payload, User $user): SocialChannel
    {
        abort_unless($this->isConfigured(), 422, 'Threads não configurado.');

        if (!empty($payload['error'])) {
            $reason = $payload['error_description'] ?? $payload['error'] ?? 'Erro desconhecido';
            $this->logActivity('threads.connection.failed', null, ['reason' => $reason, 'user_id' => $user->id]);
            abort(422, 'Autorização Threads recusada: ' . $reason);
        }

        abort_unless(!empty($payload['code']), 422, 'Código OAuth ausente na resposta do Threads.');

        $this->logActivity('threads.connection.started', $user, ['user_id' => $user->id]);

        $tokenData   = $this->exchangeCodeForToken($payload['code']);
        $longToken   = $this->getLongLivedToken($tokenData['access_token']);
        $profile     = $this->getThreadsProfile($longToken['access_token']);
        $channel     = $this->saveConnection($user, $profile, $longToken);

        $this->logActivity('threads.connection.completed', $user, [
            'channel_id'      => $channel->id,
            'threads_user_id' => $channel->threads_user_id,
            'account_name'    => $channel->account_name,
        ]);

        return $channel;
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::post('https://graph.threads.net/oauth/access_token', [
            'client_id'     => config('threads.app_id'),
            'client_secret' => config('threads.app_secret'),
            'redirect_uri'  => config('threads.redirect_uri'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        abort_unless($response->successful(), 422,
            'Falha ao obter token Threads: ' . ($response->json('error_message') ?? 'Erro da API'));

        $data = $response->json();
        abort_unless(!empty($data['access_token']), 422, 'Token Threads ausente na resposta.');

        return $data;
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->baseUrl}/access_token", [
            'grant_type'        => 'th_exchange_token',
            'client_secret'     => config('threads.app_secret'),
            'access_token'      => $shortLivedToken,
        ]);

        if ($response->failed()) {
            Log::warning('[ThreadsAuth] Could not exchange long-lived token — using short-lived: ' . $this->sanitizeError($response->body()));
            return ['access_token' => $shortLivedToken, 'expires_in' => 3600, 'token_type' => 'bearer'];
        }

        $data = $response->json();
        return [
            'access_token' => $data['access_token'] ?? $shortLivedToken,
            'expires_in'   => $data['expires_in'] ?? (60 * 24 * 60 * 60),
            'token_type'   => $data['token_type'] ?? 'bearer',
        ];
    }

    public function getThreadsProfile(string $accessToken): array
    {
        $response = Http::get("{$this->baseUrl}/{$this->graphVersion}/me", [
            'fields'       => 'id,username,name,threads_profile_picture_url,threads_biography',
            'access_token' => $accessToken,
        ]);

        abort_unless($response->successful(), 422,
            'Falha ao buscar perfil Threads: ' . ($response->json('error.message') ?? 'Erro da API'));

        return $response->json();
    }

    public function saveConnection(User $user, array $profile, array $tokenData): SocialChannel
    {
        $companyId = $user->company_id;
        $expiresAt = isset($tokenData['expires_in'])
            ? now()->addSeconds((int) $tokenData['expires_in'])
            : now()->addDays(60);

        $existing = SocialChannel::where('platform', 'threads')
            ->where('company_id', $companyId)
            ->whereNull('client_id')
            ->first();

        $data = [
            'company_id'       => $companyId,
            'client_id'        => null,
            'platform'         => 'threads',
            'threads_user_id'  => $profile['id'] ?? null,
            'external_account_id' => $profile['id'] ?? null,
            'account_name'     => $profile['username'] ?? $profile['name'] ?? null,
            'account_url'      => isset($profile['username']) ? 'https://threads.net/@' . $profile['username'] : null,
            'profile_picture_url' => $profile['threads_profile_picture_url'] ?? null,
            'access_token'     => $tokenData['access_token'],
            'refresh_token'    => null,
            'token_expires_at' => $expiresAt,
            'status'           => 'connected',
            'last_error'       => null,
            'last_checked_at'  => now(),
            'permissions'      => config('threads.scopes', []),
            'metadata'         => [
                'connected_by' => $user->id,
                'connected_at' => now()->toISOString(),
                'profile'      => [
                    'id'       => $profile['id'] ?? null,
                    'username' => $profile['username'] ?? null,
                ],
            ],
        ];

        if ($existing) {
            $existing->update($data);
            return $existing->fresh();
        }

        return SocialChannel::create($data);
    }

    public function disconnect(SocialChannel $channel): void
    {
        abort_unless($channel->isThreads(), 422, 'Canal não é Threads.');

        $channel->markDisconnected();
        $channel->update(['threads_user_id' => null]);

        $this->logActivity('threads.connection.disconnected', null, ['channel_id' => $channel->id]);
    }

    public function checkConnection(SocialChannel $channel): array
    {
        abort_unless($channel->isThreads(), 422, 'Canal não é Threads.');

        if (!$channel->hasValidToken()) {
            $channel->markError('Token ausente ou expirado.');
            $this->logActivity('threads.connection.checked', null, ['channel_id' => $channel->id, 'status' => 'token_invalid']);
            return ['connected' => false, 'reason' => 'Token inválido ou expirado.'];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->graphVersion}/me", [
                'fields'       => 'id,username',
                'access_token' => $channel->access_token,
            ]);

            if ($response->failed()) {
                $error = $this->sanitizeError($response->json('error.message') ?? $response->body());
                $channel->markError($error);
                $this->logActivity('threads.connection.checked', null, ['channel_id' => $channel->id, 'status' => 'api_error', 'error' => $error]);
                return ['connected' => false, 'reason' => $error];
            }

            $profile = $response->json();
            $channel->markConnected([
                'threads_user_id' => $profile['id'] ?? $channel->threads_user_id,
                'account_name'    => $profile['username'] ?? $channel->account_name,
            ]);

            $this->logActivity('threads.connection.checked', null, ['channel_id' => $channel->id, 'status' => 'ok']);
            return ['connected' => true, 'profile' => ['id' => $profile['id'] ?? null, 'username' => $profile['username'] ?? null]];

        } catch (Throwable $e) {
            $safe = $this->sanitizeError($e);
            $channel->markError($safe);
            $this->logActivity('threads.connection.checked', null, ['channel_id' => $channel->id, 'status' => 'exception', 'error' => $safe]);
            return ['connected' => false, 'reason' => $safe];
        }
    }

    public function sanitizeError(Throwable|string|array $error): string
    {
        if ($error instanceof Throwable) {
            $msg = $error->getMessage();
        } elseif (is_array($error)) {
            $msg = json_encode($error);
        } else {
            $msg = (string) $error;
        }
        // Remove any token-like strings
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
