<x-layouts.app title="Aprovações">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#f1f5f9;margin-bottom:4px;">Aprovações</h1>
        <p style="font-size:.85rem;color:#64748b;">Conteúdos e ações aguardando sua revisão e decisão.</p>
    </div>
</div>

@if(session('success'))
<div style="background:#0f2a1a;border:1px solid #166534;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#4ade80;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <div style="background:#0f172a;border:1px solid #78350f;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Pendentes</div>
        <div style="font-size:2rem;font-weight:700;color:#fbbf24;">{{ $stats['pending'] }}</div>
    </div>
    <div style="background:#0f172a;border:1px solid #1e293b;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Alterações Solicitadas</div>
        <div style="font-size:2rem;font-weight:700;color:#fb923c;">{{ $stats['changes_requested'] }}</div>
    </div>
    <div style="background:#0f172a;border:1px solid #166534;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Aprovadas</div>
        <div style="font-size:2rem;font-weight:700;color:#4ade80;">{{ $stats['approved'] }}</div>
    </div>
    <div style="background:#0f172a;border:1px solid #1e293b;border-radius:12px;padding:20px;">
        <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Total</div>
        <div style="font-size:2rem;font-weight:700;color:#94a3b8;">{{ $stats['total'] }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <select name="status" style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:.8rem;">
        <option value="">Todos os status</option>
        @foreach(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','changes_requested'=>'Alterações Solicitadas','canceled'=>'Cancelado'] as $val=>$label)
        <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
        @endforeach
    </select>
    <select name="type" style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:.8rem;">
        <option value="">Todos os tipos</option>
        @foreach(['post'=>'Post','campaign'=>'Campanha','blog'=>'Blog','website_page'=>'Página Website','ai_task'=>'Tarefa IA','other'=>'Outro'] as $val=>$label)
        <option value="{{ $val }}" {{ request('type')===$val?'selected':'' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" style="background:#1e293b;color:#94a3b8;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
    @if(request()->hasAny(['status','type']))
    <a href="{{ route('client.approvals.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;padding:8px 0;">Limpar</a>
    @endif
</form>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #1e293b;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Título</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tipo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Criado em</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvals as $approval)
            <tr style="border-bottom:1px solid #0f172a;{{ $approval->isPending() ? 'background:rgba(120,53,15,.05);' : '' }}" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='{{ $approval->isPending() ? 'rgba(120,53,15,.05)' : 'transparent' }}'">
                <td style="padding:12px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#e2e8f0;">
                        @if($approval->isPending()) <span style="color:#fbbf24;">●</span> @endif
                        {{ $approval->title }}
                    </div>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:#1e293b;color:#94a3b8;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $approval->approval_type_label }}</span>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:12px;">{{ $approval->status_label }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;text-align:right;">
                    <a href="{{ route('client.approvals.show', $approval->id) }}" style="background:#1e293b;color:#6b8fff;font-size:.75rem;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;display:inline-block;">Ver</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:48px 16px;text-align:center;color:#475569;font-size:.875rem;">
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
