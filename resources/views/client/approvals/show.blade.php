<x-layouts.app title="Aprovação #{{ $approvalRequest->id }}">

@php
    $approval  = $approvalRequest;
    $content   = $display['content']   ?? [];
    $visual    = $display['visual']    ?? [];
    $ctxData   = $display['ctx_data']  ?? [];
    $task      = $display['task']      ?? null;
    $employee  = $display['employee']  ?? null;
    $entity    = $display['entity']    ?? null;
    $entityType = $entity ? class_basename(get_class($entity)) : null;
    $isBlog     = ($content['type'] ?? '') === 'blog_post';
    $isSocial   = in_array($content['type'] ?? '', ['social_post','carousel','reel','story']);
    $isCarousel = ($content['type'] ?? '') === 'carousel';
    $hasImage   = !empty($visual['has_image']);
@endphp

<div style="margin-bottom:24px;">
    <a href="{{ route('client.approvals.index') }}" style="color:#4f46e5;font-size:.8rem;text-decoration:none;">← Voltar para Aprovações</a>
    <h1 style="font-size:1.3rem;font-weight:700;color:#0f172a;margin-top:8px;line-height:1.3;">{{ $approval->title }}</h1>
    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
        <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approval->status_label }}</span>
        <span style="background:#f1f5f9;color:#64748b;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approval->approval_type_label }}</span>
        @if($employee)
        <span style="background:#ede9fe;color:#5b21b6;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">🤖 {{ $employee->name }}</span>
        @endif
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#b91c1c;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

{{-- Decision --}}
@if($approval->isPending() && app(\App\Services\Approval\ApprovalService::class)->canUserApprove(auth()->user(), $approval))
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;margin-bottom:20px;">
    <h2 style="font-size:.88rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Sua Decisão</h2>
    <div style="margin-bottom:12px;">
        <textarea id="notesField" rows="2" style="width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;" placeholder="Observações (opcional)..."></textarea>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <form method="POST" action="{{ route('client.approvals.approve', $approval->id) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="notes" id="notesApprove">
            <button type="submit" onclick="document.getElementById('notesApprove').value=document.getElementById('notesField').value" style="background:#16a34a;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">✓ Aprovar</button>
        </form>
        <form method="POST" action="{{ route('client.approvals.reject', $approval->id) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="notes" id="notesReject">
            <button type="submit" onclick="document.getElementById('notesReject').value=document.getElementById('notesField').value" style="background:#dc2626;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">✕ Rejeitar</button>
        </form>
        <form method="POST" action="{{ route('client.approvals.request-changes', $approval->id) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="notes" id="notesChanges">
            <button type="submit" onclick="document.getElementById('notesChanges').value=document.getElementById('notesField').value" style="background:#d97706;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">↩ Pedir Alteração</button>
        </form>
    </div>
</div>
@elseif(!$approval->isPending())
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 20px;margin-bottom:20px;">
    <span style="font-size:.875rem;color:#64748b;">Status: </span>
    <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.8rem;font-weight:600;padding:4px 14px;border-radius:12px;">{{ $approval->status_label }}</span>
    @if($approval->isApproved())
    <span style="font-size:.8rem;color:#16a34a;margin-left:10px;">por {{ $approval->approvedBy?->name ?? '—' }} em {{ $approval->approved_at?->format('d/m/Y H:i') }}</span>
    @endif
</div>
@endif

{{-- Content --}}
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;">
    <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Conteúdo Gerado</h2>

    @if(!empty($content['missing']) && $content['missing'])
    <div style="background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:16px;color:#78350f;font-size:.875rem;">
        ⚠ O conteúdo gerado não está disponível para visualização.
    </div>

    @elseif($isBlog)
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Título</div>
        <div style="font-size:1.1rem;font-weight:700;color:#0f172a;">{{ $content['title'] ?? '—' }}</div>
    </div>
    @if(!empty($content['excerpt']))
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Resumo</div>
        <div style="font-size:.875rem;color:#334155;line-height:1.6;background:#f8fafc;border-radius:8px;padding:12px;">{{ $content['excerpt'] }}</div>
    </div>
    @endif
    @if(!empty($content['content_html']))
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Artigo Completo</div>
        <div style="font-size:.9rem;color:#1e293b;line-height:1.8;background:#f8fafc;border-radius:8px;padding:16px;max-height:500px;overflow-y:auto;">
            {!! nl2br(e($content['content_html'])) !!}
        </div>
    </div>
    @elseif(!empty($content['content_markdown']))
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Artigo Completo</div>
        <pre style="font-size:.875rem;color:#334155;white-space:pre-wrap;line-height:1.7;background:#f8fafc;border-radius:8px;padding:16px;max-height:500px;overflow-y:auto;">{{ $content['content_markdown'] }}</pre>
    </div>
    @endif
    @if(!empty($content['cta_final']))
    <div style="background:#ede9fe;border:1px solid #c4b5fd;border-radius:10px;padding:14px;margin-bottom:12px;">
        <div style="font-size:.7rem;color:#5b21b6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">CTA</div>
        <div style="font-size:.875rem;color:#3b0764;">{{ $content['cta_final'] }}</div>
    </div>
    @endif

    @elseif($isSocial)
    @if(!empty($content['title']))
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Título</div>
        <div style="font-size:1rem;font-weight:700;color:#0f172a;">{{ $content['title'] }}</div>
    </div>
    @endif
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Legenda</div>
        <div style="font-size:.9rem;color:#1e293b;line-height:1.7;white-space:pre-line;background:#f8fafc;border-radius:8px;padding:12px;">{{ $content['caption'] ?? '—' }}</div>
    </div>
    @if(!empty($content['hashtags']))
    <div style="margin-bottom:12px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Hashtags</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach((array)$content['hashtags'] as $ht)
            <span style="background:#e0f2fe;color:#0369a1;font-size:.75rem;padding:3px 8px;border-radius:8px;">{{ str_starts_with($ht,'#') ? $ht : '#'.$ht }}</span>
            @endforeach
        </div>
    </div>
    @endif
    @if(!empty($content['cta']))
    <div style="background:#ede9fe;border:1px solid #c4b5fd;border-radius:10px;padding:14px;">
        <div style="font-size:.7rem;color:#5b21b6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">CTA</div>
        <div style="font-size:.875rem;color:#3b0764;">{{ $content['cta'] }}</div>
    </div>
    @endif
    @if($isCarousel && !empty($content['slides']))
    <div style="margin-top:16px;">
        <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Slides</div>
        @foreach($content['slides'] as $idx => $slide)
        <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:10px;">
            <div style="font-size:.72rem;color:#94a3b8;margin-bottom:6px;">Slide {{ $idx+1 }}</div>
            @if(!empty($slide['headline']))<div style="font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ $slide['headline'] }}</div>@endif
            @if(!empty($slide['body']))<div style="font-size:.875rem;color:#475569;">{{ $slide['body'] }}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @else
    @if($approval->description)
    <div style="font-size:.875rem;color:#334155;line-height:1.6;">{{ $approval->description }}</div>
    @else
    <div style="font-size:.875rem;color:#94a3b8;">Conteúdo não disponível.</div>
    @endif
    @endif
</div>

{{-- Image --}}
@if($hasImage && !empty($visual['featured_url']))
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;text-align:center;">
    <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;text-align:left;">Imagem</h2>
    <img src="{{ $visual['featured_url'] }}" alt="Imagem gerada" style="max-width:100%;max-height:400px;border-radius:10px;object-fit:contain;">
    <div style="margin-top:10px;">
        <a href="{{ $visual['featured_url'] }}" target="_blank" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;">Abrir imagem</a>
    </div>
</div>
@endif

{{-- Task context (simplified) --}}
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:20px;">
    <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Contexto</h2>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Criado em</div>
            <div style="font-size:.875rem;color:#334155;">{{ $approval->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if($approval->due_at)
        <div>
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Vencimento</div>
            <div style="font-size:.875rem;color:{{ $approval->isOverdue() ? '#dc2626' : '#334155' }};">{{ $approval->due_at->format('d/m/Y H:i') }}</div>
        </div>
        @endif
        @if($employee)
        <div>
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Criado por</div>
            <div style="font-size:.875rem;color:#334155;">🤖 {{ $employee->name }}</div>
        </div>
        @endif
        @if($task)
        <div>
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Tarefa</div>
            <div style="font-size:.875rem;color:#334155;">{{ $task->title }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Comments --}}
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
    <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Comentários ({{ $approval->comments->count() }})</h2>

    @forelse($approval->comments as $comment)
    <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:.8rem;font-weight:600;color:#1e293b;">{{ $comment->user?->name ?? 'Sistema' }}</span>
            <span style="font-size:.72rem;color:#94a3b8;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <p style="font-size:.875rem;color:#475569;margin:0;line-height:1.5;">{{ $comment->comment }}</p>
    </div>
    @empty
    <p style="font-size:.875rem;color:#94a3b8;">Nenhum comentário ainda.</p>
    @endforelse

    <form method="POST" action="{{ route('client.approvals.comments', $approval->id) }}" style="margin-top:16px;">
        @csrf
        <textarea name="comment" rows="3" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;margin-bottom:10px;" placeholder="Adicione um comentário..."></textarea>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;border:none;cursor:pointer;">Comentar</button>
    </form>
</div>

</x-layouts.app>
