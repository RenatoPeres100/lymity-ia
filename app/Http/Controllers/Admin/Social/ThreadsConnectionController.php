<?php

namespace App\Http\Controllers\Admin\Social;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SocialChannel;
use App\Services\Threads\ThreadsAuthService;
use Illuminate\Http\Request;

class ThreadsConnectionController extends Controller
{
    public function __construct(private ThreadsAuthService $auth) {}

    public function index(Request $request)
    {
        $status = $this->auth->getConfigurationStatus();

        $channel = $this->auth->findChannel();

        $recentLogs = ActivityLog::where('module', 'threads')
            ->latest()
            ->limit(20)
            ->get();

        $this->log('threads.connection.viewed', $request->user());

        return view('admin.social.threads.index', compact('status', 'channel', 'recentLogs'));
    }

    public function connect(Request $request)
    {
        if (!$this->auth->isConfigured()) {
            $status  = $this->auth->getConfigurationStatus();
            $missing = implode(', ', $status['missing_vars']);
            return redirect()->route('admin.social.threads.index')
                ->withErrors(['threads' => "Threads não configurado. Variáveis ausentes: {$missing}"]);
        }

        try {
            $url = $this->auth->getAuthorizationUrl($request->user());
            return redirect()->away($url);
        } catch (\Throwable $e) {
            return redirect()->route('admin.social.threads.index')
                ->withErrors(['threads' => 'Erro ao iniciar conexão: ' . $e->getMessage()]);
        }
    }

    public function callback(Request $request)
    {
        try {
            $channel = $this->auth->handleCallback($request->all(), $request->user());
            return redirect()->route('admin.social.threads.index')
                ->with('success', 'Threads conectado com sucesso! Conta: @' . ($channel->account_name ?? $channel->threads_user_id));
        } catch (\Throwable $e) {
            $safe = $this->auth->sanitizeError($e);
            $this->log('threads.connection.failed', $request->user(), ['error' => $safe]);
            return redirect()->route('admin.social.threads.index')
                ->withErrors(['threads' => 'Falha na conexão Threads: ' . $safe]);
        }
    }

    public function disconnect(Request $request)
    {
        $channel = $this->auth->findChannel();

        if ($channel) {
            $this->auth->disconnect($channel, $request->user());
        }

        return redirect()->route('admin.social.threads.index')
            ->with('success', 'Canal Threads desconectado com sucesso.');
    }

    public function check(Request $request)
    {
        $channel = $this->auth->findChannel();

        if (!$channel) {
            return redirect()->route('admin.social.threads.index')
                ->withErrors(['threads' => 'Canal Threads não encontrado. Conecte primeiro.']);
        }

        $result = $this->auth->checkConnection($channel);

        if ($result['connected']) {
            return redirect()->route('admin.social.threads.index')
                ->with('success', 'Conexão Threads validada! Conta: @' . ($result['profile']['username'] ?? $channel->account_name));
        }

        return redirect()->route('admin.social.threads.index')
            ->withErrors(['threads' => 'Falha na verificação: ' . ($result['reason'] ?? 'Erro desconhecido')]);
    }

    private function log(string $action, $user, array $metadata = []): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'action'      => $action,
                'module'      => 'threads',
                'level'       => 'info',
                'description' => "Threads: {$action}",
                'metadata'    => $metadata,
            ]);
        } catch (\Throwable) {}
    }
}
