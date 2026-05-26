<x-layouts.app title="Memórias IA">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Memórias IA</h1>
        <p style="font-size:.85rem;color:#64748b;">Base de conhecimento persistente dos funcionários IA.</p>
    </div>
    <a href="{{ route('admin.ai-memories.create') }}" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;text-decoration:none;">+ Nova Memória</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif

<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <form method="GET" style="display:flex;gap:10px;align-items:center;">
        <select name="employee" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os funcionários</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
            @endforeach
        </select>
        <select name="type" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;color:#334155;font-size:.8rem;">
            <option value="">Todos os tipos</option>
            @foreach(['brand'=>'Marca','preference'=>'Preferência','feedback'=>'Feedback','performance'=>'Desempenho','rule'=>'Regra','insight'=>'Insight'] as $val=>$label)
            <option value="{{ $val }}" {{ request('type')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['employee','type']))
        <a href="{{ route('admin.ai-memories.index') }}" style="color:#6b8fff;font-size:.8rem;text-decoration:none;">Limpar</a>
        @endif
    </form>
</div>

<div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Título</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Tipo</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Funcionário</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Cliente</th>
                <th style="text-align:left;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Peso</th>
                <th style="text-align:right;padding:12px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($memories as $memory)
            @php
                $typeColors = ['brand'=>['#0c2a3b','#22d3ee'],'preference'=>['#1a0a2e','#a78bfa'],'feedback'=>['#052e16','#4ade80'],'performance'=>['#0c1a0a','#86efac'],'rule'=>['#451a03','#fb923c'],'insight'=>['#0c1a2e','#60a5fa']];
                $tc = $typeColors[$memory->memory_type] ?? $typeColors['insight'];
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;">
                    <div style="font-size:.875rem;font-weight:600;color:#334155;">{{ $memory->title }}</div>
                    <div style="font-size:.72rem;color:#475569;margin-top:2px;">{{ mb_substr($memory->content, 0, 70) }}...</div>
                </td>
                <td style="padding:12px 16px;">
                    <span style="background:{{ $tc[0] }};color:{{ $tc[1] }};font-size:.7rem;font-weight:600;padding:3px 10px;border-radius:10px;">{{ $memory->memory_type_label }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $memory->employee?->name ?? '—' }}</td>
                <td style="padding:12px 16px;font-size:.8rem;color:#94a3b8;">{{ $memory->client?->name ?? 'Global' }}</td>
                <td style="padding:12px 16px;font-size:.875rem;font-weight:700;color:#334155;">{{ $memory->weight }}</td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('admin.ai-memories.edit', $memory) }}" style="color:#6b8fff;font-size:.78rem;text-decoration:none;font-weight:600;">Editar</a>
                        <form method="POST" action="{{ route('admin.ai-memories.destroy', $memory) }}" onsubmit="return confirm('Remover memória?')">@csrf @method('DELETE')
                            <button type="submit" style="background:transparent;color:#f87171;font-size:.78rem;border:none;cursor:pointer;padding:0;">Remover</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:48px;text-align:center;color:#475569;font-size:.875rem;">Nenhuma memória cadastrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($memories->hasPages())
<div style="margin-top:20px;">{{ $memories->links() }}</div>
@endif

</x-layouts.app>
