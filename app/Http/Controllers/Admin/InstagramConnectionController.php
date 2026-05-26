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
        $configured  = $this->auth->isConfigured();
        $publishingEnabled = config('meta.instagram_publishing_enabled', false);

        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNull('client_id')
            ->first();

        $this->log('instagram_connection_viewed', request()->user());

        return view('admin.social.instagram.index', compact(
            'configured', 'publishingEnabled', 'channel'
        ));
    }

    public function connect()
    {
        if (!$this->auth->isConfigured()) {
            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => 'Meta não configurado. Defina META_APP_ID, META_APP_SECRET e META_REDIRECT_URI no .env.']);
        }

        $this->log('instagram_connect_started', request()->user());

        return redirect()->away($this->auth->getAuthorizationUrl());
    }

    public function callback(Request $request)
    {
        try {
            $channel = $this->auth->handleCallback($request->all(), $request->user());

            return redirect()->route('admin.social.instagram.index')
                ->with('success', "Instagram conectado com sucesso! Conta: {$channel->account_name}");
        } catch (\Throwable $e) {
            $this->log('instagram_connection_failed', $request->user(), ['error' => $e->getMessage()]);

            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => 'Falha ao conectar: ' . $e->getMessage()]);
        }
    }

    public function disconnect(Request $request)
    {
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNull('client_id')
            ->first();

        if ($channel) {
            $this->auth->disconnect($channel, $request->user());
        }

        return redirect()->route('admin.social.instagram.index')
            ->with('success', 'Instagram desconectado.');
    }

    public function check(Request $request)
    {
        $channel = SocialChannel::where('platform', 'instagram')
            ->whereNull('client_id')
            ->first();

        if (!$channel) {
            return redirect()->route('admin.social.instagram.index')
                ->withErrors(['meta' => 'Nenhum canal Instagram configurado.']);
        }

        $this->auth->refreshConnectionStatus($channel);
        $this->log('instagram_connection_checked', $request->user(), ['channel_id' => $channel->id]);

        return redirect()->route('admin.social.instagram.index')
            ->with('success', 'Status da conexão verificado.');
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
