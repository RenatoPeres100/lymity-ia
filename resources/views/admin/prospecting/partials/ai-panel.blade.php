<div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
    <div style="display:flex;align-items:center;gap:.5rem;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:1.5rem;height:1.5rem;background:#f5f3ff;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            <svg width="12" height="12" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>
        </div>
        <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">SDR IA</h3>
    </div>
    <div style="padding:1rem;display:flex;flex-direction:column;gap:.5rem;">

        @if(!$canGenerateAi)
        <p style="font-size:.8rem;color:#94a3b8;">Sem permissão para usar o SDR IA.</p>
        @else

        @php
        $btnStyle = 'width:100%;text-align:left;background:#f8fafc;color:#374151;border:1px solid #e2e8f0;padding:.5rem .75rem;border-radius:.5rem;cursor:pointer;font-size:.8rem;';
        @endphp

        @if(auth()->user()->hasPermission('prospecting.ai.analyze'))
        <form action="{{ route('admin.prospecting.ai.analyze', $lead) }}" method="POST">
            @csrf
            <button type="submit" style="{{ $btnStyle }}">🔍 Analisar lead</button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.qualify'))
        <form action="{{ route('admin.prospecting.ai.qualify', $lead) }}" method="POST">
            @csrf
            <button type="submit" style="{{ $btnStyle }}">✅ Qualificar lead</button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.suggest_next_action'))
        <form action="{{ route('admin.prospecting.ai.suggest-next-action', $lead) }}" method="POST">
            @csrf
            <button type="submit" style="{{ $btnStyle }}">→ Sugerir próxima ação</button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.analyze'))
        <form action="{{ route('admin.prospecting.ai.summarize', $lead) }}" method="POST">
            @csrf
            <button type="submit" style="{{ $btnStyle }}">📋 Resumir histórico</button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('prospecting.ai.generate_message'))
        <div style="padding-top:.5rem;border-top:1px solid #f1f5f9;">
            <p style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .4rem;">Gerar mensagem IA</p>
            <form action="{{ route('admin.prospecting.ai.generate-message', $lead) }}" method="POST"
                  style="display:flex;flex-direction:column;gap:.4rem;">
                @csrf
                <select name="channel" required
                        style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .6rem;font-size:.8rem;width:100%;">
                    <option value="">Canal...</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                    <option value="email">E-mail</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="call_script">Script de ligação</option>
                </select>
                <select name="objective" required
                        style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .6rem;font-size:.8rem;width:100%;">
                    <option value="">Objetivo...</option>
                    <option value="first_contact">Primeiro contato</option>
                    <option value="follow_up">Follow-up</option>
                    <option value="reengagement">Reengajamento</option>
                    <option value="meeting_invite">Convite para reunião</option>
                    <option value="proposal_follow_up">Follow-up de proposta</option>
                </select>
                <button type="submit"
                        style="background:#7c3aed;color:#fff;padding:.5rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;font-weight:500;">
                    Gerar mensagem IA
                </button>
            </form>
        </div>
        @endif

        @endif
    </div>

    {{-- Insights gerados --}}
    @if($lead->insights->count() > 0)
    <div style="border-top:1px solid #f1f5f9;">
        <div style="padding:.75rem 1rem .25rem;">
            <p style="font-size:.7rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin:0;">Insights IA</p>
        </div>
        <div style="padding:.5rem 1rem 1rem;display:flex;flex-direction:column;gap:.5rem;">
            @foreach($lead->insights->take(5) as $insight)
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:.5rem;padding:.75rem;">
                <div style="font-size:.7rem;font-weight:600;color:#7c3aed;margin-bottom:.3rem;">
                    {{ $insight->type_label }}
                    @if($insight->score !== null) · Score: {{ $insight->score }}/100 @endif
                </div>
                <p style="font-size:.75rem;color:#374151;margin:0;line-height:1.4;">{{ Str::limit($insight->content, 200) }}</p>

                @if($insight->insight_type === 'next_action' && isset($insight->payload['next_action']))
                <form action="{{ route('admin.prospecting.activities.store', $lead) }}" method="POST" style="margin-top:.5rem;">
                    @csrf
                    <input type="hidden" name="activity_type" value="follow_up">
                    <input type="hidden" name="title" value="{{ $insight->payload['next_action'] ?? $insight->content }}">
                    @if(!empty($insight->payload['suggested_due_date']))
                    <input type="hidden" name="due_at" value="{{ $insight->payload['suggested_due_date'] }}">
                    @endif
                    <button type="submit"
                            style="font-size:.7rem;color:#7c3aed;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;">
                        + Criar atividade com esta sugestão
                    </button>
                </form>
                @endif

                <div style="font-size:.65rem;color:#94a3b8;margin-top:.3rem;">{{ $insight->created_at->format('d/m H:i') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
