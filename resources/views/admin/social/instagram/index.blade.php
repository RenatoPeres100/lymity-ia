<x-layouts.app title="Conexão Instagram">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Conexão Instagram</h1>
                <p class="text-sm text-gray-500 mt-0.5">Perfil oficial: <strong>@lymity.ia</strong></p>
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
                            A sessão Meta expirou ou o token foi revogado. Clique em <strong>Reconectar Instagram</strong> para refazer a conexão.
                            O histórico de posts não será afetado.
                        </p>
                        @if($channel->last_error)
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400 font-mono bg-red-100 dark:bg-red-900/30 px-2 py-1 rounded">
                                {{ $channel->last_error }}
                            </p>
                        @endif
                        <div class="mt-3 flex gap-3">
                            <a href="{{ route('admin.social.instagram.connect') }}"
                               class="{{ !$configured ? 'opacity-50 pointer-events-none' : '' }} inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Reconectar Instagram
                            </a>
                            <form method="POST" action="{{ route('admin.social.instagram.disconnect') }}"
                                  onsubmit="return confirm('Desconectar o canal antigo?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-300 text-red-700 dark:text-red-400 hover:bg-red-100 text-sm font-medium transition">
                                    Desconectar canal antigo
                                </button>
                            </form>
                        </div>
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                            Este fluxo usa o <strong>Instagram Business Login</strong> (instagram_business_login). Você será redirecionado para o Instagram para autorizar a conta @lymity.ia diretamente.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Meta not configured warning --}}
        @if(!$configured)
            <div class="rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-amber-800 dark:text-amber-200">Meta/Instagram não configurado</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Configure as variáveis abaixo no <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">.env</code> do servidor:</p>
                        <pre class="mt-2 text-xs bg-amber-100 dark:bg-amber-900 rounded p-3 text-amber-900 dark:text-amber-100">META_APP_ID=seu_app_id
META_APP_SECRET=seu_app_secret
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
INSTAGRAM_PUBLISHING_ENABLED=false</pre>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Após editar: <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">php artisan config:clear && php artisan optimize:clear</code></p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status cards row --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Meta App</div>
                @if($configured)
                    <span class="inline-flex items-center gap-1 text-green-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-green-500"></span> Configurado</span>
                @else
                    <span class="inline-flex items-center gap-1 text-red-600 font-semibold text-sm"><span class="w-2 h-2 rounded-full bg-red-500"></span> Não configurado</span>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Publicação</div>
                @if($publishingEnabled)
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

        {{-- Channel info --}}
        @if($channel)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Conta Conectada</h3>
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div>
                    <span class="text-gray-500">Perfil:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $channel->account_name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="ml-2 font-medium
                        {{ $channel->isConnected() ? 'text-green-600' : ($channel->isExpired() ? 'text-red-600' : 'text-amber-600') }}">
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
                    <span class="text-gray-500">Instagram User ID:</span>
                    @if($channel->instagram_user_id)
                        <span class="ml-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $channel->instagram_user_id }}</span>
                    @else
                        <span class="ml-2 text-red-500 text-xs">ausente — reconecte</span>
                    @endif
                </div>
                <div>
                    <span class="text-gray-500">Facebook Page ID:</span>
                    @if($channel->facebook_page_id)
                        <span class="ml-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $channel->facebook_page_id }}</span>
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
                @if($channel->last_refreshed_at)
                <div>
                    <span class="text-gray-500">Última renovação:</span>
                    <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $channel->last_refreshed_at->format('d/m/Y H:i') }} ({{ $channel->last_refreshed_at->diffForHumans() }})</span>
                </div>
                @endif
                @if($channel->refresh_due_at)
                <div>
                    <span class="text-gray-500">Próxima renovação automática:</span>
                    <span class="ml-2 {{ $channel->refresh_due_at->isPast() ? 'text-amber-600 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $channel->refresh_due_at->format('d/m/Y') }}
                        ({{ $channel->refresh_due_at->diffForHumans() }})
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
                <a href="{{ route('admin.social.instagram.connect') }}"
                   class="{{ !$configured ? 'opacity-50 pointer-events-none' : '' }} inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    @if($channel && $channel->isConnected())
                        Reconectar Instagram
                    @elseif($channel && in_array($channel->status, ['needs_reconnect', 'expired', 'error']))
                        Reconectar Instagram
                    @else
                        Conectar Instagram
                    @endif
                </a>

                @if($channel)
                <form method="POST" action="{{ route('admin.social.instagram.check') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Verificar Conexão
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.social.instagram.disconnect') }}"
                      onsubmit="return confirm('Desconectar Instagram? O token será removido.')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hover:bg-red-100 text-sm font-medium transition">
                        Desconectar
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Diagnostic block --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Diagnóstico de Configuração</h3>
            <div class="space-y-2 text-sm font-mono">
                @php
                    $diag = $diagnostics;
                    $ok   = '<span class="text-green-600 font-semibold">[OK]</span>';
                    $err  = '<span class="text-red-600 font-semibold">[ERRO]</span>';
                @endphp

                <div>{!! $diag['app_id_set'] ? $ok : $err !!} META_APP_ID configurado</div>
                <div>{!! $diag['app_secret_set'] ? $ok : $err !!} META_APP_SECRET configurado (valor oculto)</div>
                @php
                    $isIgMode       = $diag['auth_mode'] === 'instagram_business_login';
                    $isFbMode       = in_array($diag['auth_mode'], ['facebook_business_login', 'facebook_login']);
                    $legacyScopesDetected = !empty(array_intersect($diag['scopes'] ?? [], ['instagram_basic','instagram_content_publish','pages_show_list','pages_read_engagement']));
                    $businessScopesOk = in_array('instagram_business_basic', $diag['scopes'] ?? []) && in_array('instagram_business_content_publish', $diag['scopes'] ?? []);
                    $endpointOk = $isIgMode; // instagram_business_login is the required mode
                @endphp
                <div class="{{ $isIgMode ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                    {!! $isIgMode ? $ok : $err !!} META_AUTH_MODE = {{ $diag['auth_mode'] }}
                </div>
                @if($isIgMode)
                <div class="text-green-700 dark:text-green-400 pl-2 text-xs">Modo correto: Instagram Business Login usa <code>www.instagram.com/oauth/authorize</code> — compatível com instagram_business_* scopes.</div>
                @elseif($isFbMode)
                <div class="text-red-700 dark:text-red-400 pl-2 text-xs font-semibold">Modo errado: facebook_business_login usa facebook.com/dialog/oauth — INCOMPATÍVEL com instagram_business_* scopes (causa Invalid Scopes). Configure META_AUTH_MODE=instagram_business_login no .env e rode php artisan optimize:clear.</div>
                @endif

                @if($isIgMode)
                <div class="text-green-700 dark:text-green-400 pl-2 text-xs">Endpoint OAuth: https://www.instagram.com/oauth/authorize</div>
                @else
                <div class="text-red-700 dark:text-red-400 pl-2 text-xs">Endpoint gerado: https://www.facebook.com/.../dialog/oauth (ERRADO para instagram_business_* scopes)</div>
                @endif

                <div class="text-gray-600 dark:text-gray-400">META_GRAPH_VERSION = {{ $diag['graph_version'] }}</div>
                <div class="{{ $diag['redirect_uri_ok'] ? 'text-green-700' : 'text-red-700' }}">
                    {!! $diag['redirect_uri_ok'] ? $ok : $err !!} META_REDIRECT_URI = {{ $diag['redirect_uri'] ?: '(não definido)' }}
                </div>
                @if(!$diag['redirect_uri_ok'])
                <div class="text-amber-700 pl-6 text-xs">Esperado: https://ia.lymity.com.br/admin/social/instagram/callback</div>
                @endif
                <div class="text-gray-600 dark:text-gray-400">INSTAGRAM_PUBLISHING_ENABLED = {{ $diag['publishing_enabled'] ? 'true' : 'false' }}</div>
                @if($channel && $channel->last_checked_at)
                <div class="text-gray-600 dark:text-gray-400">Última verificação do canal: {{ $channel->last_checked_at->format('d/m/Y H:i:s') }} ({{ $channel->last_checked_at->diffForHumans() }})</div>
                @endif
                @if(!empty($diag['scopes']))
                <div class="{{ $businessScopesOk ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                    {!! $businessScopesOk ? $ok : $err !!} Scopes: {{ implode(', ', $diag['scopes']) }}
                </div>
                @if($legacyScopesDetected)
                <div class="text-red-700 dark:text-red-400 pl-6 text-xs font-semibold">
                    Escopos antigos detectados. Atualize .env para:<br>
                    META_INSTAGRAM_SCOPES=instagram_business_basic,instagram_business_content_publish
                </div>
                @elseif($businessScopesOk && $isIgMode)
                <div class="text-green-700 dark:text-green-400 pl-6 text-xs">Escopos corretos para Instagram Business Login.</div>
                @endif
                @endif

                {{-- Invalid Scopes error --}}
                @if($isFbMode && $businessScopesOk)
                <div class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 rounded text-xs text-red-700 dark:text-red-300 font-semibold">
                    Configuração inválida: instagram_business_* scopes não devem ser enviados para facebook.com/dialog/oauth.<br>
                    Configure <code>META_AUTH_MODE=instagram_business_login</code> no .env e rode <code>php artisan optimize:clear</code>.
                </div>
                @endif
            </div>
        </div>

        {{-- Meta App configuration instructions --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-5">
            <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-3">Configuração necessária no Meta Developers</h3>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                No painel <a href="https://developers.facebook.com/apps" target="_blank" class="underline">developers.facebook.com</a>, abra seu app e configure:
            </p>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200">App Domains</p>
                    <code class="block mt-1 bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded px-3 py-1.5 text-xs">ia.lymity.com.br</code>
                </div>
                <div>
                    <p class="font-semibold text-blue-800 dark:text-blue-200">Valid OAuth Redirect URIs <span class="text-xs font-normal text-blue-600">(exatamente este valor)</span></p>
                    <code class="block mt-1 bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded px-3 py-1.5 text-xs">https://ia.lymity.com.br/admin/social/instagram/callback</code>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    @foreach([
                        'Client OAuth Login'           => 'ON',
                        'Web OAuth Login'              => 'ON',
                        'Use Strict Mode for Redirects'=> 'ON',
                        'Enforce HTTPS'                => 'ON',
                    ] as $setting => $value)
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                        <span class="text-blue-700 dark:text-blue-300">{{ $setting }}: <strong>{{ $value }}</strong></span>
                    </div>
                    @endforeach
                </div>
                <div class="text-xs text-blue-600 dark:text-blue-400 pt-1 border-t border-blue-200 dark:border-blue-700">
                    Atenção: a Redirect URI deve ser idêntica — sem barra final, sem query string, obrigatoriamente HTTPS.
                </div>
                <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-700">
                    <p class="font-semibold text-blue-800 dark:text-blue-200">Permissões obrigatórias no app Meta</p>
                    <div class="mt-1 space-y-1 text-xs text-blue-700 dark:text-blue-300">
                        <div><code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">instagram_business_basic</code> — acesso à conta Instagram Business</div>
                        <div><code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">instagram_business_content_publish</code> — publicação de conteúdo</div>
                    </div>
                    <p class="mt-2 text-xs text-amber-700 dark:text-amber-400">
                        Se aparecer <strong>Invalid Scopes</strong>, confirme se o app Meta aprovado usa as permissões
                        <code>instagram_business_basic</code> e <code>instagram_business_content_publish</code>.
                        Escopos antigos (<code>instagram_basic</code>, <code>instagram_content_publish</code>) não são mais aceitos.
                    </p>
                </div>
            </div>
        </div>

        {{-- Checklist --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Checklist de Configuração</h3>
            @php
                $scopesOk = in_array('instagram_business_basic', $diagnostics['scopes'] ?? [])
                    && in_array('instagram_business_content_publish', $diagnostics['scopes'] ?? []);

                $checks = [
                    ['label' => 'Conta Instagram é profissional (Business ou Creator)', 'done' => null],
                    ['label' => 'Instagram está vinculado a uma Página do Facebook', 'done' => null],
                    ['label' => 'App criado em Meta Developers (developers.facebook.com)', 'done' => null],
                    ['label' => 'App Domain configurado: ia.lymity.com.br', 'done' => null],
                    ['label' => 'Valid OAuth Redirect URI: https://ia.lymity.com.br/admin/social/instagram/callback', 'done' => $diagnostics['redirect_uri_ok']],
                    ['label' => 'META_APP_ID configurado no .env', 'done' => $diagnostics['app_id_set']],
                    ['label' => 'META_APP_SECRET configurado no .env', 'done' => $diagnostics['app_secret_set']],
                    ['label' => 'Escopos business: instagram_business_basic, instagram_business_content_publish', 'done' => $scopesOk],
                    ['label' => 'INSTAGRAM_PUBLISHING_ENABLED=false (manter até validar conexão)', 'done' => !$diagnostics['publishing_enabled']],
                    ['label' => 'Canal Instagram conectado com instagram_user_id preenchido', 'done' => $channel?->isConnected() && !empty($channel?->instagram_user_id)],
                    ['label' => 'Canal Instagram conectado com facebook_page_id preenchido', 'done' => $channel?->isConnected() && !empty($channel?->facebook_page_id)],
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

        {{-- Publishing status --}}
        @if(!$publishingEnabled)
        <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 p-4 text-sm text-blue-700 dark:text-blue-300">
            <strong>Publicação desabilitada</strong> — <code>INSTAGRAM_PUBLISHING_ENABLED=false</code>.
            Ative somente após validar a conexão e os testes. Para ativar: altere para <code>INSTAGRAM_PUBLISHING_ENABLED=true</code> no <code>.env</code> e rode <code>php artisan config:clear</code>.
        </div>
        @endif

    </div>

</x-layouts.app>
