<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-violet-100 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <h3 class="card-title">SDR IA</h3>
        </div>
    </div>
    <div class="card-body space-y-2">

        @if(!$canGenerateAi)
        <p class="text-sm text-slate-400">Você não tem permissão para usar o SDR IA.</p>
        @else

        @if(auth()->user()->hasPermission('prospecting.ai.analyze'))
        <form action="{{ route('admin.prospecting.ai.analyze', $lead) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm w-full text-left">
                <svg class="w-3.5 h-3.5 inline mr-1 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                Analisar lead
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.qualify'))
        <form action="{{ route('admin.prospecting.ai.qualify', $lead) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm w-full text-left">
                <svg class="w-3.5 h-3.5 inline mr-1 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Qualificar lead
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.suggest_next_action'))
        <form action="{{ route('admin.prospecting.ai.suggest-next-action', $lead) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm w-full text-left">
                <svg class="w-3.5 h-3.5 inline mr-1 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                Sugerir próxima ação
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.analyze'))
        <form action="{{ route('admin.prospecting.ai.summarize', $lead) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm w-full text-left">
                <svg class="w-3.5 h-3.5 inline mr-1 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                Resumir histórico
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.generate_message'))
        <div class="pt-2 border-t border-slate-100">
            <label class="form-label text-xs">Gerar mensagem personalizada</label>
            <form action="{{ route('admin.prospecting.ai.generate-message', $lead) }}" method="POST" class="space-y-2">
                @csrf
                <select name="channel" class="input-field w-full text-sm" required>
                    <option value="">Canal...</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                    <option value="email">E-mail</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="call_script">Script de ligação</option>
                </select>
                <select name="objective" class="input-field w-full text-sm" required>
                    <option value="">Objetivo...</option>
                    <option value="first_contact">Primeiro contato</option>
                    <option value="follow_up">Follow-up</option>
                    <option value="reengagement">Reengajamento</option>
                    <option value="meeting_invite">Convite para reunião</option>
                    <option value="proposal_follow_up">Follow-up de proposta</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm w-full">
                    Gerar mensagem IA
                </button>
            </form>
        </div>
        @endif

        @endif
    </div>

    {{-- Insights gerados --}}
    @if($lead->insights->count() > 0)
    <div class="border-t border-slate-100">
        <div class="px-4 py-2">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Insights IA</p>
        </div>
        <div class="space-y-2 px-4 pb-4">
            @foreach($lead->insights->take(5) as $insight)
            <div class="p-3 bg-violet-50 rounded-xl">
                <div class="text-xs font-semibold text-violet-700 mb-1">
                    {{ $insight->type_label }}
                    @if($insight->score !== null) · Score: {{ $insight->score }}/100 @endif
                </div>
                <p class="text-xs text-slate-700">{{ Str::limit($insight->content, 200) }}</p>

                @if($insight->insight_type === 'next_action' && isset($insight->payload['suggested_due_date']))
                <form action="{{ route('admin.prospecting.activities.store', $lead) }}" method="POST" class="mt-2">
                    @csrf
                    <input type="hidden" name="activity_type" value="follow_up">
                    <input type="hidden" name="title" value="{{ $insight->payload['next_action'] ?? $insight->content }}">
                    @if($insight->payload['suggested_due_date'])
                    <input type="hidden" name="due_at" value="{{ $insight->payload['suggested_due_date'] }}">
                    @endif
                    <button type="submit" class="text-xs text-violet-600 hover:underline">+ Criar atividade com esta sugestão</button>
                </form>
                @endif

                <div class="text-xs text-slate-400 mt-1">{{ $insight->created_at->format('d/m H:i') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
