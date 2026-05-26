<x-layouts.app title="{{ $aiTask->title }}">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">⚠ {{ session('error') }}</div>
@endif

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $aiTask->title }}</h1>
        <p style="font-size:.85rem;color:#64748b;"><a href="{{ route('admin.ai-tasks.index') }}" style="color:#6b8fff;text-decoration:none;">Tarefas IA</a> / Detalhes</p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
        <span style="background:{{ $aiTask->status_badge }};color:#fff;font-size:.75rem;font-weight:600;padding:5px 14px;border-radius:20px;">{{ $aiTask->status_label }}</span>

        @if($aiTask->canRun())
        <form method="POST" action="{{ route('admin.ai-tasks.run', $aiTask) }}">@csrf
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;">▶ Executar</button>
        </form>
        @endif

        @if($aiTask->canApprove())
        <form method="POST" action="{{ route('admin.ai-tasks.approve', $aiTask) }}">@csrf
            <button type="submit" style="background:#052e16;color:#4ade80;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;">✓ Aprovar</button>
        </form>
        <form method="POST" action="{{ route('admin.ai-tasks.reject', $aiTask) }}">@csrf
            <input type="hidden" name="reason" value="Rejeitado pelo administrador.">
            <button type="submit" style="background:#2d0a0a;color:#f87171;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;border:none;cursor:pointer;">✗ Rejeitar</button>
        </form>
        @endif

        @if(in_array($aiTask->status, ['queued','waiting_approval','pending_approval']))
        <form method="POST" action="{{ route('admin.ai-tasks.cancel', $aiTask) }}">@csrf
            <button type="submit" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:.8rem;font-weight:600;padding:7px 16px;border-radius:8px;cursor:pointer;">Cancelar</button>
        </form>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Output --}}
        @if($aiTask->output)
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Output Gerado</div>
            <div style="background:#070e1a;border:1px solid #1e293b;border-radius:10px;padding:18px;font-size:.85rem;color:#cbd5e1;line-height:1.8;white-space:pre-wrap;font-family:inherit;max-height:500px;overflow-y:auto;">{{ $aiTask->output }}</div>
        </div>
        @endif

        {{-- Description --}}
        @if($aiTask->description)
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Descrição</div>
            <p style="font-size:.875rem;color:#475569;line-height:1.7;margin:0;">{{ $aiTask->description }}</p>
        </div>
        @endif

        {{-- Error --}}
        @if($aiTask->error_message)
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:18px;">
            <div style="font-size:.78rem;font-weight:700;color:#f87171;margin-bottom:8px;">Mensagem de Erro</div>
            <p style="font-size:.8rem;color:#fca5a5;margin:0;font-family:monospace;">{{ $aiTask->error_message }}</p>
        </div>
        @endif

        {{-- Logs --}}
        @if($aiTask->logs->isNotEmpty())
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Logs de Execução</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach($aiTask->logs->sortBy('created_at') as $log)
                <div style="display:flex;gap:12px;align-items:flex-start;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;border-left:3px solid {{ match($log->level){ 'success'=>'#22c55e','warning'=>'#f59e0b','error'=>'#ef4444',default=>'#3b82f6'} }};">
                    <span style="font-size:.68rem;font-weight:700;color:#94a3b8;white-space:nowrap;margin-top:2px;">{{ $log->created_at->format('H:i:s') }}</span>
                    <span style="font-size:.8rem;color:#334155;">{{ $log->message }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Feedback --}}
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Deixar Feedback</div>
            <form method="POST" action="{{ route('admin.ai-tasks.feedback', $aiTask) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;">Avaliação (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" value="{{ $aiTask->feedbacks->where('user_id',auth()->id())->first()?->rating }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.72rem;color:#64748b;display:block;margin-bottom:4px;">Aprovado?</label>
                    <select name="approved" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.875rem;">
                        <option value="">—</option>
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>
            <textarea name="feedback" rows="2" placeholder="Comentário opcional..." style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.875rem;resize:vertical;margin-bottom:12px;">{{ $aiTask->feedbacks->where('user_id',auth()->id())->first()?->feedback }}</textarea>
            <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 20px;border-radius:8px;border:none;cursor:pointer;">Enviar Feedback</button>
            </form>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-wrapper" style="padding:24px;">
            <div style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;">Informações</div>
            @foreach([
                ['Funcionário', $aiTask->employee?->name ?? '—'],
                ['Cliente', $aiTask->client?->name ?? '—'],
                ['Tipo', $aiTask->task_type ?? $aiTask->type ?? '—'],
                ['Prioridade', $aiTask->priority_label],
                ['Requer aprovação', $aiTask->requires_approval ? 'Sim' : 'Não'],
                ['Ação sensível', $aiTask->sensitive_action ? '⚠️ Sim' : 'Não'],
                ['Criada em', $aiTask->created_at->format('d/m/Y H:i')],
                ['Finalizada em', $aiTask->finished_at?->format('d/m/Y H:i') ?? '—'],
            ] as [$label,$value])
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:.78rem;color:#64748b;">{{ $label }}</span>
                <span style="font-size:.8rem;color:#334155;font-weight:500;text-align:right;max-width:55%;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</x-layouts.app>
