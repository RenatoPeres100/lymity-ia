<x-layouts.app title="Aprovação #{{ $approvalRequest->id }}">

<div style="margin-bottom:24px;">
    <a href="{{ route('client.approvals.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;">← Voltar para Aprovações</a>
    <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-top:8px;">{{ $approvalRequest->title }}</h1>
    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
        <span style="background:{{ $approvalRequest->status_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approvalRequest->status_label }}</span>
        <span style="background:#1e293b;color:#94a3b8;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approvalRequest->approval_type_label }}</span>
    </div>
</div>

@if(session('success'))
<div style="background:#0f2a1a;border:1px solid #166534;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#4ade80;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

@if($errors->any())
<div style="background:#1a0a0a;border:1px solid #7f1d1d;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#f87171;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <div>
        {{-- Details --}}
        <div style="background:#0f172a;border:1px solid #1e293b;border-radius:14px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#f1f5f9;margin-bottom:16px;">Detalhes</h2>

            @if($approvalRequest->description)
            <div style="margin-bottom:16px;">
                <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Descrição</div>
                <div style="font-size:.875rem;color:#cbd5e1;background:#0a1628;border-radius:8px;padding:12px;line-height:1.6;">{{ $approvalRequest->description }}</div>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Solicitado por</div>
                    <div style="font-size:.875rem;color:#e2e8f0;">{{ $approvalRequest->requester?->name ?? '—' }}</div>
                </div>
                @if($approvalRequest->aiEmployee)
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Funcionário IA</div>
                    <div style="font-size:.875rem;color:#e2e8f0;">{{ $approvalRequest->aiEmployee->name }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Criado em</div>
                    <div style="font-size:.875rem;color:#e2e8f0;">{{ $approvalRequest->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($approvalRequest->isApproved())
                <div>
                    <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Aprovado por</div>
                    <div style="font-size:.875rem;color:#4ade80;">{{ $approvalRequest->approvedBy?->name }} em {{ $approvalRequest->approved_at?->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            @if($approvalRequest->payload)
            <div style="margin-top:16px;">
                <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Dados adicionais</div>
                <pre style="background:#0a1628;border:1px solid #1e293b;border-radius:8px;padding:12px;color:#94a3b8;font-size:.75rem;overflow-x:auto;white-space:pre-wrap;">{{ json_encode($approvalRequest->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        @if($approvalRequest->isPending() && app(\App\Services\Approval\ApprovalService::class)->canUserApprove(auth()->user(), $approvalRequest))
        <div style="background:#0f172a;border:1px solid #1e293b;border-radius:14px;padding:24px;margin-bottom:20px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#f1f5f9;margin-bottom:16px;">Sua Decisão</h2>
            <div style="margin-bottom:12px;">
                <textarea id="notesField" rows="2" style="width:100%;background:#0a1628;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;resize:vertical;" placeholder="Observações sobre sua decisão (opcional)..."></textarea>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('client.approvals.approve', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesApprove">
                    <button type="submit" onclick="document.getElementById('notesApprove').value=document.getElementById('notesField').value" style="background:#166534;color:#4ade80;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">✓ Aprovar</button>
                </form>
                <form method="POST" action="{{ route('client.approvals.reject', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesReject">
                    <button type="submit" onclick="document.getElementById('notesReject').value=document.getElementById('notesField').value" style="background:#7f1d1d;color:#f87171;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">✕ Rejeitar</button>
                </form>
                <form method="POST" action="{{ route('client.approvals.request-changes', $approvalRequest->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="notes" id="notesChanges">
                    <button type="submit" onclick="document.getElementById('notesChanges').value=document.getElementById('notesField').value" style="background:#78350f;color:#fbbf24;font-size:.8rem;font-weight:600;padding:9px 18px;border-radius:8px;border:none;cursor:pointer;">↩ Pedir Alteração</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Comments --}}
        <div style="background:#0f172a;border:1px solid #1e293b;border-radius:14px;padding:24px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#f1f5f9;margin-bottom:16px;">Comentários ({{ $approvalRequest->comments->count() }})</h2>

            @forelse($approvalRequest->comments as $comment)
            <div style="background:#0a1628;border-radius:10px;padding:14px;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.8rem;font-weight:600;color:#e2e8f0;">{{ $comment->user?->name ?? 'Sistema' }}</span>
                    <span style="font-size:.72rem;color:#475569;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p style="font-size:.875rem;color:#cbd5e1;margin:0;line-height:1.5;">{{ $comment->comment }}</p>
            </div>
            @empty
            <p style="font-size:.875rem;color:#475569;">Nenhum comentário ainda.</p>
            @endforelse

            <form method="POST" action="{{ route('client.approvals.comments', $approvalRequest->id) }}" style="margin-top:16px;">
                @csrf
                <textarea name="comment" rows="3" required style="width:100%;background:#0a1628;border:1px solid #1e293b;border-radius:8px;padding:10px 12px;color:#e2e8f0;font-size:.875rem;resize:vertical;margin-bottom:10px;" placeholder="Adicione um comentário..."></textarea>
                <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;border:none;cursor:pointer;">Comentar</button>
            </form>
        </div>
    </div>

    {{-- History --}}
    <div>
        <div style="background:#0f172a;border:1px solid #1e293b;border-radius:14px;padding:24px;">
            <h2 style="font-size:.9rem;font-weight:700;color:#f1f5f9;margin-bottom:16px;">Histórico</h2>
            @forelse($approvalRequest->actions as $action)
            <div style="display:flex;gap:10px;margin-bottom:14px;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $action->action_color }};margin-top:5px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:.8rem;font-weight:600;color:#e2e8f0;">{{ $action->action_label }}</div>
                    <div style="font-size:.75rem;color:#475569;">{{ $action->user?->name ?? 'Sistema' }}</div>
                    @if($action->notes)
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;font-style:italic;">"{{ Str::limit($action->notes, 80) }}"</div>
                    @endif
                    <div style="font-size:.7rem;color:#334155;margin-top:2px;">{{ $action->created_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            @empty
            <p style="font-size:.8rem;color:#475569;">Sem histórico.</p>
            @endforelse
        </div>
    </div>

</div>

</x-layouts.app>
