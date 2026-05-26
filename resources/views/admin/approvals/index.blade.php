<x-layouts.app title="Aprovações">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Aprovações</h1>
        <p style="font-size:.85rem;color:#64748b;">Gerencie todas as solicitações de aprovação da agência.</p>
    </div>
    <a href="{{ route('admin.approvals.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;">+ Nova Aprovação</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#92400e;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;font-weight:600;">Pendentes</div>
        <div style="font-size:2rem;font-weight:700;color:#d97706;">{{ $stats['pending'] }}</div>
    </div>
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#991b1b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;font-weight:600;">Críticas</div>
        <div style="font-size:2rem;font-weight:700;color:#dc2626;">{{ $stats['critical'] }}</div>
    </div>
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#9a3412;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;font-weight:600;">Vencidas</div>
        <div style="font-size:2rem;font-weight:700;color:#ea580c;">{{ $stats['overdue'] }}</div>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#166534;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;font-weight:600;">Aprovadas (mês)</div>
        <div style="font-size:2rem;font-weight:700;color:#16a34a;">{{ $stats['approved'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <select name="status" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os status</option>
            @foreach(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','changes_requested'=>'Alterações Solicitadas','canceled'=>'Cancelado'] as $val=>$label)
            <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="type" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os tipos</option>
            @foreach(['post'=>'Post','campaign'=>'Campanha','budget'=>'Orçamento','blog'=>'Blog','website_page'=>'Página Website','proposal'=>'Proposta','external_action'=>'Ação Externa','ai_task'=>'Tarefa IA','other'=>'Outro'] as $val=>$label)
            <option value="{{ $val }}" {{ request('type')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="client_id" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os clientes</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ request('client_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="level" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os níveis</option>
            @foreach(['low'=>'Baixo','medium'=>'Médio','high'=>'Alto','critical'=>'Crítico'] as $val=>$label)
            <option value="{{ $val }}" {{ request('level')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['status','type','client_id','level']))
        <a href="{{ route('admin.approvals.index') }}" style="color:#4f46e5;font-size:.8rem;text-decoration:none;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Título</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Cliente</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tipo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Nível</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Vencimento</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Criado em</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvals as $approval)
            <tr style="border-bottom:1px solid #f1f5f9;{{ $approval->isCritical() && $approval->isPending() ? 'background:rgba(254,226,226,.5);' : '' }}" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $approval->isCritical() && $approval->isPending() ? 'rgba(254,226,226,.5)' : '' }}'">
                <td style="padding:12px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#1e293b;">
                        {{ $approval->isCritical() ? '🔴 ' : '' }}{{ $approval->title }}
                    </div>
                    @if($approval->aiEmployee)
                    <div style="font-size:.72rem;color:#475569;">IA: {{ $approval->aiEmployee->name }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $approval->client?->name ?? '—' }}</td>
                <td style="padding:12px 16px;">
                    <span style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $approval->approval_type_label }}</span>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $approval->status_label }}</span>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $approval->sensitive_badge_color }};color:#fff;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $approval->sensitive_level_label }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:{{ $approval->isOverdue() ? '#f87171' : '#94a3b8' }};">
                    {{ $approval->due_at ? $approval->due_at->format('d/m/Y') : '—' }}
                    @if($approval->isOverdue()) <span style="color:#f87171;">(vencida)</span> @endif
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;text-align:right;">
                    <a href="{{ route('admin.approvals.show', $approval->id) }}" style="background:#eff6ff;color:#4f46e5;border:1px solid #e0e7ff;font-size:.75rem;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;display:inline-block;">Ver</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">
                    Nenhuma solicitação de aprovação encontrada.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($approvals->hasPages())
<div style="margin-top:20px;">{{ $approvals->links() }}</div>
@endif

</x-layouts.app>
