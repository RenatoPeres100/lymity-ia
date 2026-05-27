<x-layouts.app title="{{ $client->name }}">

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.82rem;color:#16a34a;">
    {{ session('success') }}
</div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('admin.clients.index') }}" style="display:inline-flex;align-items:center;color:#64748b;text-decoration:none;font-size:.82rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="15 18 9 12 15 6"/></svg>
            Clientes
        </a>
        <span style="color:#cbd5e1;">/</span>
        <span style="font-size:.82rem;color:#0f172a;font-weight:600;">{{ $client->name }}</span>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @can('update', $client)
        <a href="{{ route('admin.clients.edit', $client) }}" style="display:inline-flex;align-items:center;gap:6px;background:#4a6cf7;color:#fff;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
            Editar
        </a>
        @endcan
        @if($client->status === 'active')
        @can('disable', $client)
        <form method="POST" action="{{ route('admin.clients.disable', $client) }}" style="display:inline;" onsubmit="return confirm('Desativar este cliente?')">
            @csrf @method('PATCH')
            <button type="submit" style="display:inline-flex;align-items:center;padding:8px 16px;background:#fef9c3;color:#ca8a04;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;">Desativar</button>
        </form>
        @endcan
        @elseif($client->status === 'inactive')
        @can('activate', $client)
        <form method="POST" action="{{ route('admin.clients.activate', $client) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" style="display:inline-flex;align-items:center;padding:8px 16px;background:#dcfce7;color:#16a34a;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;">Ativar</button>
        </form>
        @endcan
        @endif
        @if($client->status !== 'archived')
        @can('archive', $client)
        <form method="POST" action="{{ route('admin.clients.archive', $client) }}" style="display:inline;" onsubmit="return confirm('Arquivar este cliente?')">
            @csrf @method('PATCH')
            <button type="submit" style="display:inline-flex;align-items:center;padding:8px 16px;background:#f1f5f9;color:#94a3b8;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;">Arquivar</button>
        </form>
        @endcan
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

    {{-- Left column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Dados principais --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin:0;">Dados principais</h3>
                @if($client->status === 'active')
                <span style="display:inline-block;padding:2px 10px;background:#dcfce7;color:#16a34a;border-radius:99px;font-size:.72rem;font-weight:600;">Ativo</span>
                @elseif($client->status === 'inactive')
                <span style="display:inline-block;padding:2px 10px;background:#fef9c3;color:#ca8a04;border-radius:99px;font-size:.72rem;font-weight:600;">Inativo</span>
                @else
                <span style="display:inline-block;padding:2px 10px;background:#f1f5f9;color:#94a3b8;border-radius:99px;font-size:.72rem;font-weight:600;">Arquivado</span>
                @endif
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Nome</div>
                    <div style="font-size:.88rem;color:#0f172a;font-weight:600;">{{ $client->name }}</div>
                </div>
                @if($client->legal_name)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Razão social</div>
                    <div style="font-size:.88rem;color:#0f172a;">{{ $client->legal_name }}</div>
                </div>
                @endif
                @if($client->tax_id)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">CNPJ / CPF</div>
                    <div style="font-size:.88rem;color:#0f172a;">{{ $client->tax_id }}</div>
                </div>
                @endif
                @if($client->segment)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Segmento</div>
                    <div style="font-size:.88rem;color:#0f172a;">{{ $client->segment }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Cadastrado em</div>
                    <div style="font-size:.88rem;color:#0f172a;">{{ $client->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Contato --}}
        @if($client->email || $client->phone || $client->whatsapp || $client->website)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Contato</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                @if($client->email)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">E-mail</div>
                    <a href="mailto:{{ $client->email }}" style="font-size:.85rem;color:#4a6cf7;text-decoration:none;">{{ $client->email }}</a>
                </div>
                @endif
                @if($client->phone)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Telefone</div>
                    <div style="font-size:.85rem;color:#0f172a;">{{ $client->phone }}</div>
                </div>
                @endif
                @if($client->whatsapp)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">WhatsApp</div>
                    <a href="https://wa.me/{{ $client->whatsapp }}" target="_blank" style="font-size:.85rem;color:#16a34a;text-decoration:none;">{{ $client->whatsapp }}</a>
                </div>
                @endif
                @if($client->website)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:2px;">Site</div>
                    <a href="{{ $client->website }}" target="_blank" style="font-size:.85rem;color:#4a6cf7;text-decoration:none;">{{ $client->website }}</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Redes Sociais --}}
        @if($client->instagram || $client->facebook || $client->linkedin || $client->tiktok || $client->youtube)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Redes Sociais</h3>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                @if($client->instagram)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#fdf2f8;border-radius:8px;font-size:.82rem;color:#9d174d;font-weight:600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    {{ $client->instagram }}
                </span>
                @endif
                @if($client->facebook)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#eff6ff;border-radius:8px;font-size:.82rem;color:#1d4ed8;font-weight:600;">Facebook: {{ $client->facebook }}</span>
                @endif
                @if($client->linkedin)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#f0f9ff;border-radius:8px;font-size:.82rem;color:#0369a1;font-weight:600;">LinkedIn: {{ $client->linkedin }}</span>
                @endif
                @if($client->tiktok)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#fdf2f8;border-radius:8px;font-size:.82rem;color:#6b21a8;font-weight:600;">TikTok: {{ $client->tiktok }}</span>
                @endif
                @if($client->youtube)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#fff7ed;border-radius:8px;font-size:.82rem;color:#c2410c;font-weight:600;">YouTube</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Usuários --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin:0;">Usuários ({{ $client->users->count() }})</h3>
                @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create', ['client_id' => $client->id, 'user_type' => 'client']) }}" style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;color:#475569;font-size:.75rem;font-weight:600;padding:6px 12px;border-radius:6px;text-decoration:none;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Vincular usuário
                </a>
                @endcan
            </div>
            @forelse($client->users as $user)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                <div>
                    <div style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $user->name }}</div>
                    <div style="font-size:.72rem;color:#64748b;">{{ $user->email }} · {{ $user->role }}</div>
                </div>
                <span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600;{{ $user->status === 'active' ? 'background:#dcfce7;color:#16a34a;' : 'background:#f1f5f9;color:#94a3b8;' }}">
                    {{ $user->status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
            </div>
            @empty
            <div style="text-align:center;padding:24px;color:#94a3b8;font-size:.82rem;">Nenhum usuário vinculado.</div>
            @endforelse
        </div>

        {{-- Logs recentes --}}
        @if($recentLogs->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Atividade recente</h3>
            @foreach($recentLogs as $log)
            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f8fafc;' : '' }}">
                <div style="width:6px;height:6px;background:#4a6cf7;border-radius:50%;margin-top:6px;flex-shrink:0;"></div>
                <div>
                    <div style="font-size:.8rem;color:#0f172a;">{{ $log->description }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">{{ $log->created_at->format('d/m/Y H:i') }}{{ $log->user ? ' · ' . $log->user->name : '' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Estratégia --}}
        @if($client->brand_voice || $client->target_audience || $client->offer_description)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Estratégia da marca</h3>
            @if($client->brand_voice)
            <div style="margin-bottom:14px;">
                <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:4px;">Tom de voz</div>
                <div style="font-size:.82rem;color:#374151;line-height:1.5;">{{ $client->brand_voice }}</div>
            </div>
            @endif
            @if($client->target_audience)
            <div style="margin-bottom:14px;">
                <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:4px;">Público-alvo</div>
                <div style="font-size:.82rem;color:#374151;line-height:1.5;">{{ $client->target_audience }}</div>
            </div>
            @endif
            @if($client->offer_description)
            <div>
                <div style="font-size:.72rem;font-weight:600;color:#64748b;margin-bottom:4px;">Oferta principal</div>
                <div style="font-size:.82rem;color:#374151;line-height:1.5;">{{ $client->offer_description }}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Observações --}}
        @if($client->notes)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:10px;">Observações internas</h3>
            <div style="font-size:.82rem;color:#374151;line-height:1.5;white-space:pre-line;">{{ $client->notes }}</div>
        </div>
        @endif

        {{-- Aprovações pendentes --}}
        @if($pendingApprovals > 0)
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:20px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#92400e;margin-bottom:6px;">Aprovações pendentes</h3>
            <div style="font-size:1.5rem;font-weight:700;color:#d97706;">{{ $pendingApprovals }}</div>
            <a href="{{ route('admin.approvals.index', ['client_id' => $client->id]) }}" style="display:inline-block;margin-top:8px;font-size:.78rem;color:#b45309;text-decoration:none;font-weight:600;">Ver aprovações →</a>
        </div>
        @endif

        {{-- Links rápidos --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;">
            <h3 style="font-size:.88rem;font-weight:700;color:#0f172a;margin-bottom:14px;">Acesso rápido</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('admin.clients.blog.index', $client) }}" style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:#f8fafc;border-radius:8px;text-decoration:none;color:#374151;font-size:.82rem;font-weight:600;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                    Blog
                </a>
                <a href="{{ route('admin.clients.brand.show', $client) }}" style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:#f8fafc;border-radius:8px;text-decoration:none;color:#374151;font-size:.82rem;font-weight:600;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="13.5" cy="6.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688z"/></svg>
                    Marca
                </a>
                <a href="{{ route('admin.clients.website.show', $client) }}" style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:#f8fafc;border-radius:8px;text-decoration:none;color:#374151;font-size:.82rem;font-weight:600;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10z"/></svg>
                    Website
                </a>
            </div>
        </div>

    </div>
</div>

</x-layouts.app>
