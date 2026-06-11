<x-layouts.app title="Conexão Threads">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Conexão Threads</h1>
                <p class="text-sm text-gray-500 mt-0.5">Canal separado do Instagram — não compartilha token</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm space-y-1">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        {{-- Needs reconnect / expired / error alert --}}
        @if($channel && in_array($channel->status, ['needs_reconnect', 'expired', 'error']))
            <div class="rounded-xl border border-red-300 bg-red-50 dark:bg-red-900/20 p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-red-800 dark:text-red-200">
                            @if($channel->status === 'needs_reconnect')
                                Reconexão necessária — token expirado ou revogado
                            @elseif($channel->status === 'expired')
                                Token expirado — clique em Reconectar
                            @else
                                Erro de conexão — canal precisa ser reconectado
                            @endif
                        </h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                            O token Threads expirou ou foi revogado. Clique em <strong>Reconectar Threads</strong> para refazer a conexão.
                        </p>
                        @if($channel->last_error)
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400 font-mono bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded">
                                {{ $channel->last_error }}
                            </p>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('admin.social.threads.connect') }}"
                               class="{{ !$status['configured'] ? 'opacity-50 pointer-events-none' : '' }} inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Reconectar Threads
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Not configured warning --}}
        @if(!$status['configured'])
            <div class="rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-amber-800 dark:text-amber-200">Threads não configurado</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                            Variáveis ausentes:
                            @foreach($status['missing_vars'] as $v)
                                <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">{{ $v }}</code>
                            @endforeach
                        </p>

                        @if($status['use_meta_app'])
                        <pre class="mt-2 text-xs bg-amber-100 dark:bg-amber-900 rounded p-3 text-amber-900 dark:text-amber-100">{{-- OPÇÃO A: Reaproveitar app Meta (THREADS_USE_META_APP=true) --}}
META_APP_ID=seu_app_id           # já configurado se Instagram funciona
META_APP_SECRET=seu_app_secret   # já configurado se Instagram funciona
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_USE_META_APP=true</pre>
                        @else
                        <pre class="mt-2 text-xs bg-amber-100 dark:bg-amber-900 rounded p-3 text-amber-900 dark:text-amber-100">{{-- OPÇÃO B: App dedicado Threads --}}
THREADS_APP_ID=seu_threads_app_id
THREADS_APP_SECRET=seu_threads_app_secret
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_USE_META_APP=false</pre>
                        @endif

                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                            Após editar: <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">php artisan config:clear && php artisan optimize:clear</code>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status cards --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">App Threads</div>
                @if($status['configured'])
                    <span class="inline-flex items-center gap-1 text-green-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-green-500"></span> Configurado</span>
                @else
                    <span class="inline-flex items-center gap-1 text-red-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-red-500"></span> Não configurado</span>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Publicação</div>
                @if($status['publishing_enabled'])
                    <span class="inline-flex items-center gap-1 text-green-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-green-500"></span> Habilitada</span>
                @else
                    <span class="inline-flex items-center gap-1 text-amber-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Desabilitada</span>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Canal</div>
                @if($channel && $channel->isConnected())
                    <span class="inline-flex items-center gap-1 text-green-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Conectado</span>
                @elseif($channel)
                    <span class="inline-flex items-center gap-1 text-gray-500 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ $channel->status_label }}</span>
                @else
                    <span class="inline-flex items-center gap-1 text-gray-400 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-gray-300"></span> Sem canal</span>
                @endif
            </div>
        </div>

        {{-- Connected channel info --}}
        @if($channel)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Conta Conectada</h3>
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div>
                    <span class="text-gray-500">Perfil:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        @if($channel->account_name)@{{ $channel->account_name }}@else<span class="text-gray-400">—</span>@endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="ml-2 font-medium {{ $channel->isConnected() ? 'text-green-600' : ($channel->isExpired() ? 'text-red-600' : 'text-amber-600') }}">
                        {{ $channel->status_label }}
                    </span>
                </div>
                @if($channel->account_url)
                <div>
                    <span class="text-gray-500">URL:</span>
                    <a href="{{ $channel->account_url }}" target="_blank" class="ml-2 text-indigo-600 hover:underline">{{ $channel->account_url }}</a>
                </div>
                @endif
                <div>
                    <span class="text-gray-500">Threads User ID:</span>
                    @if($channel->threads_user_id)
                        <span class="ml-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $channel->threads_user_id }}</span>
                    @else
                        <span class="ml-2 text-red-500 text-xs">ausente — reconecte</span>
                    @endif
                </div>
                @if($channel->token_expires_at)
                <div>
                    <span class="text-gray-500">Token expira em:</span>
                    <span class="ml-2 {{ $channel->token_expires_at->isPast() ? 'text-red-600 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $channel->token_expires_at->format('d/m/Y H:i') }}
                        ({{ $channel->token_expires_at->diffForHumans() }})
                    </span>
                </div>
                @endif
                @if($channel->last_checked_at)
                <div>
                    <span class="text-gray-500">Última verificação:</span>
                    <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $channel->last_checked_at->diffForHumans() }}</span>
                </div>
                @endif
                @if($channel->disconnected_at && $channel->status === 'disconnected')
                <div>
                    <span class="text-gray-500">Desconectado em:</span>
                    <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $channel->disconnected_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($channel->last_error)
                <div class="col-span-2">
                    <span class="text-gray-500">Último erro:</span>
                    <span class="ml-2 text-red-600 text-xs">{{ $channel->last_error }}</span>
                </div>
                @endif
            </div>

            @if(is_array($channel->permissions) && count($channel->permissions) > 0)
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Permissões concedidas</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($channel->permissions as $perm)
                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-mono">{{ $perm }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Actions --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Ações</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.social.threads.connect') }}"
                   class="{{ !$status['configured'] ? 'opacity-50 pointer-events-none' : '' }} inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    @if($channel && $channel->isConnected())
                        Reconectar Threads
                    @elseif($channel && in_array($channel->status, ['needs_reconnect', 'expired', 'error']))
                        Reconectar Threads
                    @else
                        Conectar Threads
                    @endif
                </a>

                @if($channel)
                <form method="POST" action="{{ route('admin.social.threads.check') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Verificar Conexão
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.social.threads.disconnect') }}"
                      onsubmit="return confirm('Desconectar Threads? O token será removido.')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hover:bg-red-100 text-sm font-medium transition">
                        Desconectar
                    </button>
                </form>

                <a href="{{ route('admin.social.threads.posts.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 text-sm font-medium transition border border-gray-200 dark:border-gray-600">
                    Ver Posts
                </a>
                @endif
            </div>
        </div>

        {{-- Diagnostic block --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Diagnóstico de Configuração</h3>
            <div class="space-y-2 text-sm font-mono">
                @php
                    $s    = $status;
                    $ok   = '<span class="text-green-600 font-semibold">[OK]</span>';
                    $err  = '<span class="text-red-600 font-semibold">[ERRO]</span>';
                    $warn = '<span class="text-amber-600 font-semibold">[AVISO]</span>';
                    $info = '<span class="text-blue-600 font-semibold">[INFO]</span>';
                @endphp

                {{-- App Mode --}}
                <div>
                    {!! $info !!}
                    THREADS_USE_META_APP = {{ $s['use_meta_app'] ? 'true' : 'false' }}
                    &nbsp;→&nbsp;
                    <span class="{{ $s['use_meta_app'] ? 'text-blue-700' : 'text-gray-600' }} dark:text-gray-300">
                        Modo: {{ $s['app_mode'] === 'meta_app_reused' ? 'Meta App reutilizado (Instagram App)' : 'App dedicado Threads' }}
                    </span>
                </div>

                @if($s['use_meta_app'])
                    {{-- Meta App mode: show META_APP_ID / META_APP_SECRET --}}
                    <div>{!! $s['raw_meta_app_id_set'] ? $ok : $err !!} META_APP_ID configurado (usado para Threads)</div>
                    <div>{!! $s['raw_meta_secret_set'] ? $ok : $err !!} META_APP_SECRET configurado (valor oculto)</div>
                    @if($s['raw_meta_app_id_set'] && $s['raw_meta_secret_set'])
                    <div class="pl-4 text-amber-700 dark:text-amber-400 text-xs">
                        {!! $warn !!} Confirme que o app Meta possui o produto/caso de uso <strong>Threads API</strong> habilitado em
                        <a href="https://developers.facebook.com/apps" target="_blank" class="underline">developers.facebook.com</a>.
                    </div>
                    @endif
                    {{-- Dedicated Threads vars not required but show info if present --}}
                    @if($s['raw_threads_app_id_set'])
                    <div class="pl-4 text-gray-500 dark:text-gray-400 text-xs">{!! $info !!} THREADS_APP_ID também definido (ignorado — usando Meta App)</div>
                    @endif
                @else
                    {{-- Dedicated Threads App mode --}}
                    <div>{!! $s['raw_threads_app_id_set'] ? $ok : $err !!} THREADS_APP_ID configurado</div>
                    <div>{!! $s['raw_threads_secret_set'] ? $ok : $err !!} THREADS_APP_SECRET configurado (valor oculto)</div>
                @endif

                {{-- Redirect URI --}}
                <div class="{{ $s['redirect_uri_ok'] ? 'text-green-700' : 'text-red-700' }}">
                    {!! $s['redirect_uri_set'] ? $ok : $err !!} THREADS_REDIRECT_URI = {{ $s['redirect_uri'] ?: '(não definido)' }}
                </div>
                @if(!$s['redirect_uri_ok'] && $s['redirect_uri_set'])
                <div class="text-amber-700 pl-6 text-xs">Esperado: https://ia.lymity.com.br/admin/social/threads/callback</div>
                @endif

                {{-- Scopes --}}
                @if(!empty($s['scopes']))
                <div class="text-gray-600 dark:text-gray-400">
                    {!! $ok !!} Scopes: {{ implode(', ', $s['scopes']) }}
                </div>
                @endif

                {{-- URLs --}}
                <div class="text-gray-500 dark:text-gray-500 text-xs pt-1">OAuth URL: {{ $s['oauth_base_url'] }}</div>
                <div class="text-gray-500 dark:text-gray-500 text-xs">Token URL: {{ $s['token_url'] }}</div>
                <div class="text-gray-500 dark:text-gray-500 text-xs">Base API: {{ $s['base_url'] }}</div>
                <div class="text-gray-500 dark:text-gray-500 text-xs">Graph Version: {{ $s['graph_version'] }}</div>

                {{-- Publishing --}}
                <div class="text-gray-600 dark:text-gray-400">
                    {!! $warn !!} THREADS_PUBLISHING_ENABLED = {{ $s['publishing_enabled'] ? 'true' : 'false' }}
                    @if(!$s['publishing_enabled'])
                        <span class="text-xs ml-1">(publicação bloqueada — seguro)</span>
                    @endif
                </div>

                {{-- Channel status --}}
                <div class="{{ $s['channel_connected'] ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-500' }}">
                    {!! $s['channel_connected'] ? $ok : '<span class="text-gray-400">[—]</span>' !!}
                    Canal Threads conectado: {{ $s['channel_connected'] ? 'sim' : 'não' }}
                </div>
                @if($s['threads_user_id'])
                <div class="text-gray-500 dark:text-gray-500 text-xs pl-4">Threads User ID: {{ $s['threads_user_id'] }}</div>
                @endif
                @if($s['token_expires_at'])
                <div class="text-gray-500 dark:text-gray-500 text-xs pl-4">Token expira em: {{ $s['token_expires_at'] }}</div>
                @endif
                @if($s['last_checked_at'])
                <div class="text-gray-500 dark:text-gray-500 text-xs pl-4">Última verificação: {{ $s['last_checked_at'] }}</div>
                @endif
                @if($s['last_error'])
                <div class="text-red-600 text-xs pl-4">Último erro: {{ $s['last_error'] }}</div>
                @endif
            </div>
        </div>

        {{-- Setup instructions --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-5">
            <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-3">Configuração no .env — Duas Opções</h3>
            <div class="space-y-4 text-sm">
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200 mb-1">Opção A — Reaproveitar app Meta/Instagram (recomendado se Instagram já funciona)</p>
                    <pre class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded p-3">THREADS_USE_META_APP=true
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_SCOPES=threads_basic,threads_content_publish
THREADS_PUBLISHING_ENABLED=false
# META_APP_ID e META_APP_SECRET já devem estar definidos
# Requisito: o app Meta precisa ter Threads API habilitado</pre>
                </div>
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200 mb-1">Opção B — App dedicado Threads</p>
                    <pre class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded p-3">THREADS_USE_META_APP=false
THREADS_APP_ID=seu_threads_app_id
THREADS_APP_SECRET=seu_threads_app_secret
THREADS_REDIRECT_URI=https://ia.lymity.com.br/admin/social/threads/callback
THREADS_SCOPES=threads_basic,threads_content_publish
THREADS_PUBLISHING_ENABLED=false</pre>
                </div>
                <div class="mt-2 pt-3 border-t border-blue-200 dark:border-blue-700">
                    <p class="text-xs text-blue-700 dark:text-blue-300">Redirect URI no app Meta/Threads:</p>
                    <code class="block mt-1 bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded px-3 py-1.5 text-xs">https://ia.lymity.com.br/admin/social/threads/callback</code>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                        Após editar .env: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">php artisan config:clear && php artisan optimize:clear && php artisan threads:diagnose</code>
                    </p>
                </div>
            </div>
        </div>

        {{-- Checklist --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Checklist</h3>
            @php
                $useMetaApp = $status['use_meta_app'];
                $checks = [
                    [
                        'label' => $useMetaApp
                            ? 'META_APP_ID configurado (modo meta_app_reused)'
                            : 'THREADS_APP_ID configurado',
                        'done' => $status['app_id_set'],
                    ],
                    [
                        'label' => $useMetaApp
                            ? 'META_APP_SECRET configurado (modo meta_app_reused)'
                            : 'THREADS_APP_SECRET configurado',
                        'done' => $status['app_secret_set'],
                    ],
                    [
                        'label' => 'THREADS_REDIRECT_URI configurado',
                        'done'  => $status['redirect_uri_set'],
                    ],
                    [
                        'label' => 'Redirect URI correto: https://ia.lymity.com.br/admin/social/threads/callback',
                        'done'  => $status['redirect_uri_ok'],
                    ],
                    [
                        'label' => 'Scopes: threads_basic, threads_content_publish',
                        'done'  => in_array('threads_basic', $status['scopes']) && in_array('threads_content_publish', $status['scopes']),
                    ],
                    [
                        'label' => 'Canal Threads conectado (status=connected)',
                        'done'  => $channel?->isConnected(),
                    ],
                    [
                        'label' => 'Token válido e não expirado',
                        'done'  => $channel?->hasValidToken(),
                    ],
                    [
                        'label' => 'Threads User ID preenchido',
                        'done'  => $channel && !empty($channel->threads_user_id),
                    ],
                    [
                        'label' => 'THREADS_PUBLISHING_ENABLED=true (ative somente após validar conexão)',
                        'done'  => $status['publishing_enabled'],
                    ],
                ];
            @endphp
            <ul class="space-y-2">
                @foreach($checks as $check)
                <li class="flex items-start gap-2.5 text-sm">
                    @if($check['done'] === true)
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="text-green-700 dark:text-green-400">{{ $check['label'] }}</span>
                    @elseif($check['done'] === false)
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span class="text-red-700 dark:text-red-400">{{ $check['label'] }}</span>
                    @else
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                        <span class="text-gray-600 dark:text-gray-400">{{ $check['label'] }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Publishing disabled notice --}}
        @if(!$status['publishing_enabled'])
        <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 p-4 text-sm text-blue-700 dark:text-blue-300">
            <strong>Publicação desabilitada</strong> — <code>THREADS_PUBLISHING_ENABLED=false</code>.
            Ative somente após validar a conexão OAuth e testes. Para ativar: altere para <code>THREADS_PUBLISHING_ENABLED=true</code> no <code>.env</code> e rode <code>php artisan config:clear</code>.
        </div>
        @endif

        {{-- Recent logs --}}
        @if($recentLogs->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Logs recentes</h3>
                <a href="{{ route('admin.social.threads.logs') }}" class="text-xs text-indigo-600 hover:underline">Ver todos →</a>
            </div>
            <div class="space-y-1.5">
                @foreach($recentLogs->take(10) as $log)
                <div class="flex gap-3 items-start text-xs py-1.5 border-b border-gray-50 dark:border-gray-700">
                    <span class="font-semibold {{ in_array($log->level, ['warning','error']) ? 'text-red-600' : 'text-green-600' }} whitespace-nowrap">{{ strtoupper($log->level) }}</span>
                    <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m H:i') }}</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $log->action }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</x-layouts.app>
