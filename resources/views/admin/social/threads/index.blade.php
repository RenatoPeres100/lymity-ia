@extends('components.layouts.app')

@section('title', 'Threads — Conexão')

@section('content')
<div style="max-width:900px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;">
        <span style="font-size:1.5rem;">🧵</span>
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0;">Conexão Threads</h1>
            <p style="font-size:.85rem;color:#64748b;margin:.25rem 0 0;">Canal separado do Instagram. Não misture tokens.</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @foreach(['success','error','warning','info'] as $type)
        @if(session($type))
        <div style="background:{{ $type==='success'?'#f0fdf4':($type==='error'?'#fef2f2':'#fffbeb') }};border:1px solid {{ $type==='success'?'#86efac':($type==='error'?'#fca5a5':'#fcd34d') }};color:{{ $type==='success'?'#166534':($type==='error'?'#dc2626':'#92400e') }};padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            {{ session($type) }}
        </div>
        @endif
    @endforeach
    @if($errors->has('threads'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            {{ $errors->first('threads') }}
        </div>
    @endif

    @if(!config('features.threads_connection'))
        <div style="background:#fef9c3;border:1px solid #fde047;color:#854d0e;padding:1rem;border-radius:.75rem;">
            Feature <code>threads_connection</code> desabilitada em <code>config/features.php</code>.
        </div>
    @else

    {{-- Configuration status --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 1rem;">Configuração</h3>
        <div style="display:grid;gap:.5rem;">
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;">
                @if($status['app_id_set'])
                    <span style="color:#16a34a;">✓</span> <span>THREADS_APP_ID configurado</span>
                @else
                    <span style="color:#dc2626;">✗</span> <span style="color:#dc2626;">THREADS_APP_ID ausente</span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;">
                @if($status['app_secret_set'])
                    <span style="color:#16a34a;">✓</span> <span>THREADS_APP_SECRET configurado</span>
                @else
                    <span style="color:#dc2626;">✗</span> <span style="color:#dc2626;">THREADS_APP_SECRET ausente</span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;">
                @if($status['redirect_uri_set'])
                    <span style="color:#16a34a;">✓</span> <span>THREADS_REDIRECT_URI: <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:.25rem;">{{ $status['redirect_uri'] }}</code></span>
                @else
                    <span style="color:#dc2626;">✗</span> <span style="color:#dc2626;">THREADS_REDIRECT_URI ausente</span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;">
                @if($status['publishing_enabled'])
                    <span style="color:#16a34a;">✓</span> <span>THREADS_PUBLISHING_ENABLED=true</span>
                @else
                    <span style="color:#f59e0b;">⚠</span> <span style="color:#92400e;">THREADS_PUBLISHING_ENABLED=false (publicação bloqueada)</span>
                @endif
            </div>
            <div style="font-size:.8rem;color:#64748b;">
                Scopes: {{ implode(', ', $status['scopes']) }} &nbsp;|&nbsp; API: {{ $status['base_url'] }} &nbsp;|&nbsp; Versão: {{ $status['graph_version'] }}
            </div>
        </div>
    </div>

    {{-- Channel status --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 1rem;">Canal Threads</h3>

        @if($channel && $channel->isConnected())
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                <span style="font-weight:600;color:#166534;">Conectado</span>
            </div>
            <div style="display:grid;gap:.4rem;font-size:.85rem;color:#475569;">
                <div><strong>Conta:</strong> @{{ $channel->account_name ?? '—' }}</div>
                <div><strong>Threads User ID:</strong> {{ $channel->threads_user_id ?? '—' }}</div>
                <div><strong>Token expira em:</strong> {{ $channel->token_expires_at?->format('d/m/Y H:i') ?? '—' }}</div>
                @if($channel->permissions)
                <div><strong>Permissões:</strong> {{ implode(', ', (array)$channel->permissions) }}</div>
                @endif
                @if($channel->last_checked_at)
                <div><strong>Última verificação:</strong> {{ $channel->last_checked_at->diffForHumans() }}</div>
                @endif
                @if($channel->last_error)
                <div style="color:#dc2626;"><strong>Último erro:</strong> {{ $channel->last_error }}</div>
                @endif
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.social.threads.check') }}">
                    @csrf
                    <button style="background:#2563eb;color:#fff;padding:.5rem 1rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.85rem;">
                        Revalidar Conexão
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.social.threads.disconnect') }}"
                      onsubmit="return confirm('Desconectar canal Threads?')">
                    @csrf
                    <button style="background:#fee2e2;color:#dc2626;padding:.5rem 1rem;border-radius:.5rem;border:1px solid #fca5a5;cursor:pointer;font-size:.85rem;">
                        Desconectar
                    </button>
                </form>
                <a href="{{ route('admin.social.threads.posts.index') }}"
                   style="background:#f0fdf4;color:#166534;padding:.5rem 1rem;border-radius:.5rem;border:1px solid #86efac;text-decoration:none;font-size:.85rem;">
                    Ver Posts
                </a>
            </div>
        @elseif($channel && $channel->status === 'error')
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#dc2626;display:inline-block;animate-pulse:1;"></span>
                <span style="color:#dc2626;font-weight:600;">Erro de conexão</span>
            </div>
            @if($channel->last_error)
            <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;font-size:.8rem;margin-bottom:1rem;">
                {{ $channel->last_error }}
            </div>
            @endif
            @if($status['configured'])
            <a href="{{ route('admin.social.threads.connect') }}"
               style="display:inline-block;background:#000;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;font-weight:600;">
                🧵 Reconectar Threads
            </a>
            @endif
        @else
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#94a3b8;display:inline-block;"></span>
                <span style="color:#64748b;">Não conectado</span>
            </div>

            @if(!$status['configured'])
                <div style="background:#fffbeb;border:1px solid #fde047;color:#92400e;padding:.75rem;border-radius:.5rem;font-size:.85rem;margin-bottom:1rem;">
                    Threads não configurado. Defina THREADS_APP_ID, THREADS_APP_SECRET e THREADS_REDIRECT_URI no <code>.env</code> e rode <code>php artisan config:clear</code>.
                </div>
            @else
                <a href="{{ route('admin.social.threads.connect') }}"
                   style="display:inline-block;background:#000;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;font-weight:600;">
                    🧵 Conectar Threads
                </a>
            @endif
        @endif
    </div>

    {{-- Recent logs --}}
    @if($recentLogs->isNotEmpty())
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0;">Logs recentes</h3>
            <a href="{{ route('admin.social.threads.logs') }}" style="font-size:.8rem;color:#2563eb;text-decoration:none;">Ver todos →</a>
        </div>
        <div style="display:grid;gap:.5rem;">
            @foreach($recentLogs->take(8) as $log)
            <div style="display:flex;gap:.75rem;align-items:flex-start;font-size:.8rem;padding:.4rem 0;border-bottom:1px solid #f1f5f9;">
                <span style="color:{{ $log->level==='warning'||$log->level==='error'?'#dc2626':'#16a34a' }};font-weight:600;white-space:nowrap;">{{ $log->level }}</span>
                <span style="color:#64748b;white-space:nowrap;">{{ $log->created_at->format('d/m H:i') }}</span>
                <span style="color:#0f172a;">{{ $log->action }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end features.threads_connection --}}
</div>
@endsection
