<?php

namespace App\Http\Controllers\Admin\Social;

use App\Http\Controllers\Controller;
use App\Models\SocialChannel;
use App\Services\Threads\ThreadsAuthService;
use Illuminate\Http\Request;

class ThreadsConnectionController extends Controller
{
    public function __construct(private ThreadsAuthService $auth) {}

    public function index(Request $request)
    {
        $user    = $request->user();
        $status  = $this->auth->getConfigurationStatus();

        $channel = SocialChannel::where('platform', 'threads')
            ->where('company_id', $user->company_id)
            ->whereNull('client_id')
            ->first();

        $recentLogs = \App\Models\ActivityLog::where('module', 'threads')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.social.threads.index', compact('status', 'channel', 'recentLogs'));
    }

    public function connect(Request $request)
    {
        abort_unless($this->auth->isConfigured(), 422, 'Threads não configurado. Configure THREADS_APP_ID, THREADS_APP_SECRET e THREADS_REDIRECT_URI no .env.');
        $url = $this->auth->getAuthorizationUrl($request->user());
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        try {
            $channel = $this->auth->handleCallback($request->all(), $request->user());
            return redirect()->route('admin.social.threads.index')
                ->with('success', 'Threads conectado com sucesso! Conta: @' . ($channel->account_name ?? $channel->threads_user_id));
        } catch (\Throwable $e) {
            $safe = $this->auth->sanitizeError($e);
            return redirect()->route('admin.social.threads.index')
                ->withErrors(['threads' => 'Falha na conexão Threads: ' . $safe]);
        }
    }

    public function disconnect(Request $request)
    {
        $user    = $request->user();
        $channel = SocialChannel::where('platform', 'threads')
            ->where('company_id', $user->company_id)
            ->whereNull('client_id')
            ->firstOrFail();

        $this->auth->disconnect($channel);

        return redirect()->route('admin.social.threads.index')
            ->with('success', 'Canal Threads desconectado.');
    }

    public function check(Request $request)
    {
        $user    = $request->user();
        $channel = SocialChannel::where('platform', 'threads')
            ->where('company_id', $user->company_id)
            ->whereNull('client_id')
            ->firstOrFail();

        $result = $this->auth->checkConnection($channel);

        if ($result['connected']) {
            return redirect()->route('admin.social.threads.index')
                ->with('success', 'Conexão Threads validada! Conta: @' . ($result['profile']['username'] ?? $channel->account_name));
        }

        return redirect()->route('admin.social.threads.index')
            ->withErrors(['threads' => 'Falha na verificação: ' . ($result['reason'] ?? 'Erro desconhecido')]);
    }
}
