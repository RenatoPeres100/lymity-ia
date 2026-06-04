<x-layouts.app title="{{ $lead->name }} — Lead">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#0f172a;margin:0 0 .25rem;">{{ $lead->name }}</h1>
        <p style="color:#64748b;font-size:.9rem;margin:0;">
            {{ $lead->company_name ?: '' }}
            @if($lead->segment) · {{ $lead->segment }} @endif
        </p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @can('update', $lead)
        <a href="{{ route('admin.prospecting.leads.edit', $lead) }}"
           style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.875rem;border:1px solid #e2e8f0;">
            Editar
        </a>
        @endcan
        @can('archive', $lead)
        @if($lead->status === 'open')
        <form action="{{ route('admin.prospecting.leads.archive', $lead) }}" method="POST" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Arquivar este lead?')"
                    style="background:#f1f5f9;color:#374151;padding:.5rem 1rem;border-radius:.5rem;border:1px solid #e2e8f0;cursor:pointer;font-size:.875rem;">
                Arquivar
            </button>
        </form>
        @endif
        @endcan
        <a href="{{ route('admin.prospecting.leads.index') }}"
           style="color:#6366f1;padding:.5rem .75rem;font-size:.875rem;text-decoration:none;">
            ← Leads
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

    {{-- Coluna principal --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Dados principais --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Dados do Lead</h3>
                <span style="font-size:.75rem;padding:.2rem .5rem;border-radius:999px;font-weight:600;
                    {{ $lead->status === 'open' ? 'background:#eff6ff;color:#2563eb;' : ($lead->status === 'won' ? 'background:#f0fdf4;color:#16a34a;' : 'background:#f1f5f9;color:#64748b;') }}">
                    {{ $lead->status_label }}
                </span>
            </div>
            <div style="padding:1.25rem;">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;font-size:.875rem;">
                    @if($lead->email)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">E-mail</span>{{ $lead->email }}</div>
                    @endif
                    @if($lead->whatsapp)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">WhatsApp</span>{{ $lead->whatsapp }}</div>
                    @endif
                    @if($lead->phone)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Telefone</span>{{ $lead->phone }}</div>
                    @endif
                    @if($lead->website)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Website</span>
                        <a href="{{ $lead->website }}" target="_blank" style="color:#6366f1;">{{ $lead->website }}</a>
                    </div>
                    @endif
                    @if($lead->instagram)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Instagram</span>{{ $lead->instagram }}</div>
                    @endif
                    @if($lead->linkedin)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">LinkedIn</span>{{ $lead->linkedin }}</div>
                    @endif
                    @if($lead->city || $lead->state)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Localização</span>{{ $lead->city }}{{ $lead->city && $lead->state ? '/' : '' }}{{ $lead->state }}</div>
                    @endif
                    @if($lead->source)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Origem</span>{{ $lead->source }}</div>
                    @endif
                    @if($lead->estimated_budget)
                    <div><span style="display:block;font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Orçamento</span>{{ $lead->estimated_budget }}</div>
                    @endif
                </div>

                @if($lead->pain_points)
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <span style="font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Dores identificadas</span>
                    <p style="margin:.3rem 0 0;font-size:.875rem;color:#374151;line-height:1.5;">{{ $lead->pain_points }}</p>
                </div>
                @endif
                @if($lead->opportunity_summary)
                <div style="margin-top:.75rem;">
                    <span style="font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Resumo da oportunidade</span>
                    <p style="margin:.3rem 0 0;font-size:.875rem;color:#374151;line-height:1.5;">{{ $lead->opportunity_summary }}</p>
                </div>
                @endif
                @if($lead->notes)
                <div style="margin-top:.75rem;">
                    <span style="font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Observações</span>
                    <p style="margin:.3rem 0 0;font-size:.875rem;color:#374151;line-height:1.5;">{{ $lead->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Atividades --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Atividades & Follow-ups</h3>
            </div>
            <div style="padding:1.25rem;">
                @can('create', \App\Models\ProspectActivity::class)
                <form action="{{ route('admin.prospecting.activities.store', $lead) }}" method="POST"
                      style="background:#f8fafc;border-radius:.5rem;padding:1rem;margin-bottom:1rem;">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 2fr 1fr;gap:.5rem;margin-bottom:.5rem;">
                        <select name="activity_type" required
                                style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .6rem;font-size:.8rem;">
                            <option value="">Tipo...</option>
                            <option value="call">Ligação</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">E-mail</option>
                            <option value="instagram">Instagram</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="meeting">Reunião</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="task">Tarefa</option>
                            <option value="other">Outro</option>
                        </select>
                        <input type="text" name="title" placeholder="Título da atividade" required
                               style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .6rem;font-size:.8rem;">
                        <input type="datetime-local" name="due_at"
                               style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .6rem;font-size:.8rem;">
                    </div>
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit"
                                style="background:#6366f1;color:#fff;padding:.4rem .75rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;">
                            + Adicionar Atividade
                        </button>
                    </div>
                </form>
                @endcan

                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    @forelse($lead->activities as $activity)
                    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.75rem;border-radius:.5rem;
                        {{ $activity->status === 'done' ? 'background:#f0fdf4;' : ($activity->status === 'canceled' ? 'background:#f8fafc;opacity:.6;' : 'background:#fff;border:1px solid #e2e8f0;') }}">
                        <div style="width:2rem;height:2rem;{{ $activity->status === 'done' ? 'background:#dcfce7;' : 'background:#f1f5f9;' }}border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#64748b;flex-shrink:0;">
                            {{ strtoupper(substr($activity->type_label, 0, 2)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.875rem;font-weight:500;color:#1e293b;">{{ $activity->title }}</div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:.1rem;">
                                {{ $activity->type_label }}
                                @if($activity->due_at) · {{ $activity->due_at->format('d/m/Y H:i') }} @endif
                                @if($activity->user) · {{ $activity->user->name }} @endif
                            </div>
                            @if($activity->outcome)
                            <div style="font-size:.75rem;color:#475569;margin-top:.25rem;font-style:italic;">{{ $activity->outcome }}</div>
                            @endif
                        </div>
                        <div style="display:flex;gap:.25rem;flex-shrink:0;">
                            @if($activity->status === 'pending')
                            <form action="{{ route('admin.prospecting.activities.complete', $activity) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="Concluir"
                                        style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:.25rem .5rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;">✓</button>
                            </form>
                            <form action="{{ route('admin.prospecting.activities.cancel', $activity) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="Cancelar"
                                        style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:.25rem .5rem;border-radius:.375rem;cursor:pointer;font-size:.75rem;">✕</button>
                            </form>
                            @elseif($activity->status === 'done')
                            <span style="font-size:.7rem;background:#f0fdf4;color:#16a34a;padding:.2rem .5rem;border-radius:999px;font-weight:600;">Concluída</span>
                            @else
                            <span style="font-size:.7rem;background:#f1f5f9;color:#64748b;padding:.2rem .5rem;border-radius:999px;font-weight:600;">Cancelada</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p style="text-align:center;color:#94a3b8;font-size:.875rem;padding:1.5rem 0;">Nenhuma atividade registrada</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Notas --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Notas</h3>
            </div>
            <div style="padding:1.25rem;">
                <form action="{{ route('admin.prospecting.notes.store', $lead) }}" method="POST" style="margin-bottom:1rem;">
                    @csrf
                    <textarea name="note" rows="2" placeholder="Adicionar nota..."
                              style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem;font-size:.875rem;width:100%;box-sizing:border-box;resize:vertical;margin-bottom:.4rem;"></textarea>
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit"
                                style="background:#6366f1;color:#fff;padding:.4rem .75rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;">
                            Adicionar Nota
                        </button>
                    </div>
                </form>

                <div style="display:flex;flex-direction:column;gap:.75rem;">
                    @forelse($lead->noteEntries as $note)
                    <div style="background:#fefce8;border:1px solid #fef08a;border-radius:.5rem;padding:.75rem 1rem;">
                        <p style="font-size:.875rem;color:#374151;margin:0 0 .4rem;line-height:1.5;">{{ $note->note }}</p>
                        <div style="font-size:.7rem;color:#94a3b8;">
                            {{ $note->user?->name ?? '—' }} · {{ $note->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @empty
                    <p style="text-align:center;color:#94a3b8;font-size:.875rem;padding:1rem 0;">Nenhuma nota registrada</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Mensagens IA --}}
        @include('admin.prospecting.partials.message-suggestions', ['lead' => $lead])

    </div>

    {{-- Coluna lateral --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Pipeline & Status --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.95rem;font-weight:600;color:#0f172a;margin:0;">Pipeline & Status</h3>
            </div>
            <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <span style="font-size:.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.3rem;">Etapa atual</span>
                    <div style="display:flex;align-items:center;gap:.4rem;">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $lead->stage?->color ?? '#64748b' }};flex-shrink:0;"></span>
                        <span style="font-size:.9rem;font-weight:600;color:#1e293b;">{{ $lead->stage?->name ?? '—' }}</span>
                    </div>
                </div>

                @can('moveStage', $lead)
                <form action="{{ route('admin.prospecting.leads.move-stage', $lead) }}" method="POST">
                    @csrf @method('PATCH')
                    <label style="font-size:.75rem;font-weight:500;color:#374151;display:block;margin-bottom:.3rem;">Mover para etapa</label>
                    <select name="prospect_stage_id"
                            style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.4rem .6rem;font-size:.8rem;width:100%;margin-bottom:.5rem;">
                        <option value="">Selecionar...</option>
                        @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $stage->id === $lead->prospect_stage_id ? 'disabled' : '' }}>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            style="width:100%;background:#f1f5f9;color:#374151;padding:.4rem;border-radius:.375rem;border:1px solid #e2e8f0;cursor:pointer;font-size:.8rem;">
                        Mover Etapa
                    </button>
                </form>
                @endcan

                <div style="padding-top:.5rem;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.5rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#94a3b8;font-size:.8rem;">Interesse</span>
                        @if($lead->interest_level)
                        <span style="font-size:.75rem;padding:.15rem .4rem;border-radius:999px;font-weight:600;
                            {{ $lead->interest_level === 'hot' ? 'background:#fef2f2;color:#dc2626;' : ($lead->interest_level === 'warm' ? 'background:#fefce8;color:#a16207;' : 'background:#eff6ff;color:#2563eb;') }}">
                            {{ $lead->interest_label }}
                        </span>
                        @else <span style="color:#94a3b8;">—</span> @endif
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#94a3b8;font-size:.8rem;">Fit Score</span>
                        <span style="font-weight:700;color:#6366f1;">{{ $lead->fit_score !== null ? $lead->fit_score . '/100' : '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#94a3b8;font-size:.8rem;">Responsável</span>
                        <span style="font-size:.8rem;color:#374151;">{{ $lead->owner?->name ?? '—' }}</span>
                    </div>
                    @if($lead->next_action)
                    <div>
                        <span style="color:#94a3b8;font-size:.8rem;">Próxima ação</span>
                        <p style="margin:.2rem 0 0;font-size:.8rem;color:#374151;">{{ $lead->next_action }}</p>
                    </div>
                    @endif
                    @if($lead->next_follow_up_at)
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#94a3b8;font-size:.8rem;">Follow-up</span>
                        <span style="font-size:.8rem;{{ $lead->isOverdue() ? 'color:#dc2626;font-weight:700;' : 'color:#374151;' }}">
                            {{ $lead->next_follow_up_at->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SDR IA Panel --}}
        @include('admin.prospecting.partials.ai-panel', ['lead' => $lead, 'canGenerateAi' => $canGenerateAi])

    </div>
</div>

</x-layouts.app>
