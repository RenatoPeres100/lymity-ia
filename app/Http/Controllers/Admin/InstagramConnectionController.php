<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\SocialChannel;
use App\Services\Instagram\MetaInstagramAuthService;
use Illuminate\Http\Request;

class InstagramConnectionController extends Controller
{
    public function __construct(private MetaInstagramAuthService $auth) {}

    public function index()
    {
        $configured        = $this->auth->isConfigured();
        $publishingEnabled = config('meta.instagram_publishing_enabled', false);

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        $diagnostics = $this->buildDiagnostics($configured);

        $this->log('instagram_connection_viewed', request()->user());

        return view('admin.social.instagram.index', compact(
            'configured', 'publishingEnabled', 'channel', 'diagnostics'
        ));
    }

    public function connect(Request $request)
    {
        if (!$this->auth->isConfigured()) {
            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => 'Meta não configurado. Defina META_APP_ID, META_APP_SECRET e META_REDIRECT_URI no .env.']);
        }

        try {
            $url = $this->auth->getAuthorizationUrl($request->user());
            $this->log('instagram_connect_started', $request->user());
            return redirect()->away($url);
        } catch (\Throwable $e) {
            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => 'Erro ao iniciar conexão: ' . $e->getMessage()]);
        }
    }

    public function callback(Request $request)
    {
        try {
            $channel = $this->auth->handleCallback($request->query(), $request->user());

            return redirect()->route('admin.social.instagram.index')
                ->with('success', "Instagram conectado com sucesso! Conta: {$channel->account_name}");
        } catch (\Throwable $e) {
            $this->log('instagram_connection_failed', $request->user(), ['error' => $e->getMessage()]);

            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => $e->getMessage()]);
        }
    }

    public function disconnect(Request $request)
    {
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if ($channel) {
            $this->auth->disconnect($channel, $request->user());
        }

        return redirect()->route('admin.social.instagram.index')
            ->with('success', 'Instagram desconectado com sucesso.');
    }

    public function check(Request $request)
    {
        $company = Company::first();
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNotNull('company_id')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            // Ensure the channel exists as disconnected so the user can connect
            $channel = SocialChannel::create([
                'company_id'   => $company?->id,
                'platform'     => 'instagram',
                'account_name' => '@lymity.ia',
                'account_url'  => 'https://instagram.com/lymity.ia',
                'status'       => 'disconnected',
            ]);

            return redirect()->route('admin.social.instagram.index')
                ->with('success', 'Canal @lymity.ia criado. Clique em "Conectar Instagram" para vincular.');
        }

        $this->auth->refreshConnectionStatus($channel);
        $this->log('instagram_connection_checked', $request->user(), ['channel_id' => $channel->id]);

        return redirect()->route('admin.social.instagram.index')
            ->with('success', 'Status da conexão verificado.');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildDiagnostics(bool $configured): array
    {
        $authMode    = config('meta.auth_mode', 'instagram_business_login');
        $redirectUri = config('meta.redirect_uri', '');
        $expectedUri = 'https://ia.lymity.com.br/admin/social/instagram/callback';

        // Show the scopes that will actually be used for the current auth_mode
        $scopes = ($authMode === 'instagram_business_login')
            ? config('meta.instagram_scopes', ['instagram_business_basic', 'instagram_business_content_publish'])
            : config('meta.facebook_scopes', []);

        return [
            'app_id_set'        => !empty(config('meta.app_id')),
            'app_secret_set'    => !empty(config('meta.app_secret')),
            'auth_mode'         => $authMode,
            'graph_version'     => config('meta.graph_version', 'v25.0'),
            'redirect_uri'      => $redirectUri,
            'redirect_uri_ok'   => $redirectUri === $expectedUri,
            'scopes'            => $scopes,
            'publishing_enabled'=> config('meta.instagram_publishing_enabled', false),
        ];
    }

    private function log(string $action, $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'instagram',
                'level'       => 'info',
                'description' => "Instagram: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {}
    }
}
