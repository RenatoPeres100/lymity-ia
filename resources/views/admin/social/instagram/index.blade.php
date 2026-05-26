<x-layouts.app title="Conexão Instagram">

    <div class="max-w-3xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Conexão Instagram</h1>
                <p class="text-sm text-gray-500 mt-0.5">Perfil oficial: <strong>@lymity.ia</strong> — {{ config('meta.redirect_uri') }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
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
                        <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Configure as variáveis abaixo no <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">.env</code> do servidor e depois reinicie o cache:</p>
                        <pre class="mt-2 text-xs bg-amber-100 dark:bg-amber-900 rounded p-3 text-amber-900 dark:text-amber-100">META_APP_ID=seu_app_id
META_APP_SECRET=seu_app_secret
META_REDIRECT_URI=https://ia.lymity.com.br/admin/social/instagram/callback
META_GRAPH_VERSION=v25.0
INSTAGRAM_PUBLISHING_ENABLED=false</pre>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Após editar: <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">php artisan config:cache</code></p>
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
                @if($channel->instagram_user_id)
                <div>
                    <span class="text-gray-500">Instagram User ID:</span>
                    <span class="ml-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $channel->instagram_user_id }}</span>
                </div>
                @endif
                @if($channel->facebook_page_id)
                <div>
                    <span class="text-gray-500">Facebook Page ID:</span>
                    <span class="ml-2 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $channel->facebook_page_id }}</span>
                </div>
                @endif
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
                @if($channel->last_error)
                <div class="col-span-2">
                    <span class="text-gray-500">Último erro:</span>
                    <span class="ml-2 text-red-600 text-xs">{{ $channel->last_error }}</span>
                </div>
                @endif
            </div>

            {{-- Permissions --}}
            @if($channel->permissions && count($channel->permissions) > 0)
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
                    {{ $channel && $channel->isConnected() ? 'Reconectar Instagram' : 'Conectar Instagram' }}
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

        {{-- Checklist --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Checklist de Configuração</h3>
            @php
                $checks = [
                    ['label' => 'Conta Instagram é profissional (Business ou Creator)', 'done' => null],
                    ['label' => 'Instagram está vinculado a uma Página do Facebook', 'done' => null],
                    ['label' => 'App criado em Meta Developers (developers.facebook.com)', 'done' => null],
                    ['label' => 'Redirect URI configurada no app Meta: ' . config('meta.redirect_uri'), 'done' => null],
                    ['label' => 'Permissões adicionadas no app: pages_show_list, instagram_basic, instagram_content_publish', 'done' => null],
                    ['label' => 'META_APP_ID configurado no .env', 'done' => !empty(config('meta.app_id'))],
                    ['label' => 'META_APP_SECRET configurado no .env', 'done' => !empty(config('meta.app_secret'))],
                    ['label' => 'META_REDIRECT_URI configurado no .env', 'done' => !empty(config('meta.redirect_uri'))],
                    ['label' => 'INSTAGRAM_PUBLISHING_ENABLED=false (manter até validar conexão)', 'done' => !config('meta.instagram_publishing_enabled', false)],
                    ['label' => 'Canal Instagram conectado com sucesso', 'done' => $channel?->isConnected()],
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
            Ative apenas após validar a conexão e os testes. Para ativar: altere para <code>INSTAGRAM_PUBLISHING_ENABLED=true</code> no <code>.env</code> e rode <code>php artisan config:cache</code>.
        </div>
        @endif

    </div>

</x-layouts.app>
