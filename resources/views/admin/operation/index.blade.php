<x-layouts.app title="Central de Operação">

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Blog pendente</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $stats['blog_pending'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Instagram</div>
            <div class="text-3xl font-bold text-pink-600">{{ $stats['instagram_pending'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Aprovações</div>
            <div class="text-3xl font-bold {{ $stats['approvals_pending'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $stats['approvals_pending'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Agendados</div>
            <div class="text-3xl font-bold text-blue-600">{{ $stats['scheduled_posts'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Func. IA ativos</div>
            <div class="text-3xl font-bold text-green-600">{{ $stats['ai_employees_active'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Falhas IA (24h)</div>
            <div class="text-3xl font-bold {{ $stats['ai_failures'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $stats['ai_failures'] }}</div>
        </div>
    </div>

    {{-- Status bar --}}
    <div class="flex flex-wrap gap-3 mb-8">
        {{-- AI Provider --}}
        @if($isRealProvider)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                IA: {{ strtoupper($aiProvider) }} Ativo
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                IA: Modo Mock — Configure um provedor real para publicar conteúdo
            </span>
        @endif

        {{-- Next scheduled --}}
        @if($nextScheduled)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Próxima pub.: {{ $nextScheduled->scheduled_at->format('d/m H:i') }}
            </span>
        @endif

        {{-- Queue status (check Redis) --}}
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
            <span class="w-2 h-2 rounded-full bg-green-400"></span>
            Fila: Redis
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Blog pendente --}}
        @if(config('features.blog_pipeline'))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Blog — Pendentes</h3>
                <a href="{{ route('admin.blog.posts.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($blogPending as $post)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $post->title }}</p>
                            <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 rounded text-xs font-medium
                            {{ $post->status === 'pending_review' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $post->status }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhum post pendente</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Instagram pendente --}}
        @if(config('features.instagram_pipeline'))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Instagram — Aprovação Pendente</h3>
                <a href="{{ route('admin.social.posts.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($instagramPending as $post)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit(($post->main_caption ?? $post->title ?? $post->content) ?? '(sem legenda)', 60) }}</p>
                            <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 rounded text-xs font-medium bg-pink-100 text-pink-800">
                            aprovação
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhum post aguardando aprovação</div>
                @endforelse
            </div>
        </div>
        @endif

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Aprovações pendentes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Aprovações Pendentes</h3>
                <a href="{{ route('admin.approvals.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($approvalsPending as $approval)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($approval->title ?? $approval->description ?? 'Aprovação', 55) }}</p>
                            <p class="text-xs text-gray-500">
                                {{ optional($approval->requestedBy)->name ?? '—' }} · {{ $approval->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                            {{ $approval->approval_type }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhuma aprovação pendente</div>
                @endforelse
            </div>
        </div>

        {{-- Posts agendados --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Publicações Agendadas</h3>
                <a href="{{ route('admin.publishing-queue') }}" class="text-xs text-indigo-600 hover:underline">Fila completa</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($scheduledPosts as $post)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit(($post->main_caption ?? $post->title ?? $post->content) ?? '(post)', 55) }}</p>
                            <p class="text-xs text-gray-500">{{ optional($post->scheduled_at)->format('d/m H:i') }}</p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                            agendado
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhuma publicação agendada</div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Tarefas IA --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Tarefas IA Recentes</h3>
                <a href="{{ route('admin.ai-tasks.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($aiTasks as $task)
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($task->title ?? $task->task_type, 40) }}</p>
                            <span class="ml-2 px-1.5 py-0.5 rounded text-xs
                                {{ $task->status === 'completed' ? 'bg-green-100 text-green-700' : ($task->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $task->status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ optional($task->aiEmployee)->name ?? '—' }}</p>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhuma tarefa</div>
                @endforelse
            </div>
        </div>

        {{-- Funcionários IA --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Func. IA Ativos</h3>
                <a href="{{ route('admin.ai-employees.index') }}" class="text-xs text-indigo-600 hover:underline">Gerenciar</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                @forelse($aiEmployees as $employee)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-300">{{ Str::upper(Str::substr($employee->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $employee->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $employee->role_key }}</p>
                        </div>
                        <span class="ml-auto w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">Nenhum funcionário ativo</div>
                @endforelse
            </div>
        </div>

        {{-- Logs operacionais + Falhas --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Logs Recentes</h3>
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @forelse($recentLogs->take(5) as $log)
                        <div class="px-5 py-2">
                            <p class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $log->action }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-center text-xs text-gray-400">Sem logs</div>
                    @endforelse
                </div>
            </div>

            @if($recentFailures->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-800 shadow-sm">
                <div class="px-5 py-3 border-b border-red-100 dark:border-red-800">
                    <h3 class="font-semibold text-red-700 dark:text-red-400 text-sm">Falhas IA</h3>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($recentFailures as $failure)
                        <div class="px-5 py-2">
                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($failure->title ?? $failure->task_type, 40) }}</p>
                            <p class="text-xs text-gray-400">{{ optional($failure->aiEmployee)->name ?? '—' }} · {{ $failure->updated_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

    </div>

</x-layouts.app>
