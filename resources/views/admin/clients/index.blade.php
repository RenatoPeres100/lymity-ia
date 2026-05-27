<x-layouts.app title="Clientes">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.82rem;color:#16a34a;">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.82rem;color:#dc2626;">
    {{ session('error') }}
</div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#0f172a;margin:0;">Clientes</h2>
        <p style="font-size:.82rem;color:#64748b;margin:4px 0 0;">Gerencie os clientes atendidos pela agência.</p>
    </div>
    @can('create', App\Models\Client::class)
    <a href="{{ route('admin.clients.create') }}" style="display:inline-flex;align-items:center;gap:6px;background:#4a6cf7;color:#fff;font-size:.82rem;font-weight:600;padding:9px 16px;border-radius:8px;text-decoration:none;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo cliente
    </a>
    @endcan
</div>

{{-- Stats cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;">
        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Total</div>
        <div style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:4px;">{{ $stats['total'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;">
        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Ativos</div>
        <div style="font-size:1.6rem;font-weight:700;color:#16a34a;margin-top:4px;">{{ $stats['active'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;">
        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Inativos</div>
        <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;margin-top:4px;">{{ $stats['inactive'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;">
        <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Arquivados</div>
        <div style="font-size:1.6rem;font-weight:700;color:#94a3b8;margin-top:4px;">{{ $stats['archived'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;margin-bottom:20px;">
    <form method="GET" action="{{ route('admin.clients.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:220px;">
            <label style="display:block;font-size:.72rem;font-weight:600;color:#374151;margin-bottom:4px;">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, razão social, segmento, e-mail..."
                style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;">
        </div>
        <div style="min-width:160px;">
            <label style="display:block;font-size:.72rem;font-weight:600;color:#374151;margin-bottom:4px;">Status</label>
            <select name="status" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:.82rem;color:#0f172a;box-sizing:border-box;">
                <option value="">Ativo + Inativo</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Ativo</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Arquivado</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" style="background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">Filtrar</button>
            @if(request('search') || request('status'))
            <a href="{{ route('admin.clients.index') }}" style="background:#f1f5f9;color:#475569;font-size:.8rem;font-weight:600;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;">Limpar</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Cliente</th>
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Segmento</th>
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Contato</th>
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Instagram</th>
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Status</th>
                <th style="text-align:center;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Usuários</th>
                <th style="text-align:left;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Cadastro</th>
                <th style="text-align:right;padding:11px 16px;font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:12px 16px;">
                    <div style="font-weight:600;color:#0f172a;">{{ $client->name }}</div>
                    @if($client->legal_name && $client->legal_name !== $client->name)
                    <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">{{ $client->legal_name }}</div>
                    @endif
                </td>
                <td style="padding:12px 16px;color:#475569;">
                    {{ $client->segment ?: '—' }}
                </td>
                <td style="padding:12px 16px;">
                    @if($client->email)
                    <div style="color:#475569;">{{ $client->email }}</div>
                    @endif
                    @if($client->website)
                    <a href="{{ $client->website }}" target="_blank" style="font-size:.72rem;color:#4a6cf7;text-decoration:none;">
                        {{ parse_url($client->website, PHP_URL_HOST) ?: $client->website }}
                    </a>
                    @endif
                    @if(!$client->email && !$client->website)
                    <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;color:#475569;">{{ $client->instagram ?: '—' }}</td>
                <td style="padding:12px 16px;">
                    @if($client->status === 'active')
                    <span style="display:inline-block;padding:2px 10px;background:#dcfce7;color:#16a34a;border-radius:99px;font-size:.72rem;font-weight:600;">Ativo</span>
                    @elseif($client->status === 'inactive')
                    <span style="display:inline-block;padding:2px 10px;background:#fef9c3;color:#ca8a04;border-radius:99px;font-size:.72rem;font-weight:600;">Inativo</span>
                    @else
                    <span style="display:inline-block;padding:2px 10px;background:#f1f5f9;color:#94a3b8;border-radius:99px;font-size:.72rem;font-weight:600;">Arquivado</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;color:#475569;">{{ $client->users_count }}</td>
                <td style="padding:12px 16px;font-size:.72rem;color:#94a3b8;">{{ $client->created_at->format('d/m/Y') }}</td>
                <td style="padding:12px 16px;text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:nowrap;">
                        @can('view', $client)
                        <a href="{{ route('admin.clients.show', $client) }}" style="display:inline-flex;align-items:center;padding:5px 10px;background:#f1f5f9;color:#475569;border-radius:6px;font-size:.75rem;font-weight:600;text-decoration:none;">Ver</a>
                        @endcan
                        @can('update', $client)
                        <a href="{{ route('admin.clients.edit', $client) }}" style="display:inline-flex;align-items:center;padding:5px 10px;background:#f1f5f9;color:#475569;border-radius:6px;font-size:.75rem;font-weight:600;text-decoration:none;">Editar</a>
                        @endcan
                        @if($client->status === 'active')
                        @can('disable', $client)
                        <form method="POST" action="{{ route('admin.clients.disable', $client) }}" style="display:inline;" onsubmit="return confirm('Desativar {{ addslashes($client->name) }}?')">
                            @csrf @method('PATCH')
                            <button type="submit" style="display:inline-flex;align-items:center;padding:5px 10px;background:#fef9c3;color:#ca8a04;border-radius:6px;font-size:.75rem;font-weight:600;border:none;cursor:pointer;">Desativar</button>
                        </form>
                        @endcan
                        @elseif($client->status === 'inactive')
                        @can('activate', $client)
                        <form method="POST" action="{{ route('admin.clients.activate', $client) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" style="display:inline-flex;align-items:center;padding:5px 10px;background:#dcfce7;color:#16a34a;border-radius:6px;font-size:.75rem;font-weight:600;border:none;cursor:pointer;">Ativar</button>
                        </form>
                        @endcan
                        @endif
                        @if($client->status !== 'archived')
                        @can('archive', $client)
                        <form method="POST" action="{{ route('admin.clients.archive', $client) }}" style="display:inline;" onsubmit="return confirm('Arquivar {{ addslashes($client->name) }}? Esta ação pode ser revertida.')">
                            @csrf @method('PATCH')
                            <button type="submit" style="display:inline-flex;align-items:center;padding:5px 10px;background:#f1f5f9;color:#94a3b8;border-radius:6px;font-size:.75rem;font-weight:600;border:none;cursor:pointer;">Arquivar</button>
                        </form>
                        @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:48px 16px;text-align:center;color:#94a3b8;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;opacity:.4;display:block;">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                    <div style="font-size:.85rem;font-weight:600;">Nenhum cliente encontrado</div>
                    @can('create', App\Models\Client::class)
                    <a href="{{ route('admin.clients.create') }}" style="display:inline-block;margin-top:12px;font-size:.8rem;color:#4a6cf7;text-decoration:none;">Criar primeiro cliente →</a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($clients->hasPages())
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f1f5f9;font-size:.8rem;color:#64748b;">
        <span>{{ $clients->firstItem() }}–{{ $clients->lastItem() }} de {{ $clients->total() }} clientes</span>
        {{ $clients->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
