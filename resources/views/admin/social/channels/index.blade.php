<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;">Canais Sociais</h1>
            <a href="{{ route('admin.social.channels.create') }}" style="background:#3b82f6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">+ Novo Canal</a>
        </div>

        @if(session('success'))
        <div style="background:#10b98120;border:1px solid #10b981;color:#10b981;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        @foreach(['Plataforma','Nome','Handle','Cliente','Status','Ações'] as $h)
                        <th style="text-align:left;padding:.75rem 1rem;color:#94a3b8;font-size:.8rem;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($channels as $channel)
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:.75rem 1rem;color:#f1f5f9;font-size:.9rem;">{{ $channel->platform_emoji }} {{ $channel->platform_label }}</td>
                        <td style="padding:.75rem 1rem;color:#f1f5f9;font-size:.9rem;">{{ $channel->account_name }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $channel->handle ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $channel->client?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;">
                            <span style="font-size:.75rem;padding:.2rem .6rem;border-radius:9999px;
                                background:{{ $channel->status==='active' ? '#10b98120' : '#f59e0b20' }};
                                color:{{ $channel->status==='active' ? '#10b981' : '#f59e0b' }};">
                                {{ $channel->status_label }}
                            </span>
                        </td>
                        <td style="padding:.75rem 1rem;display:flex;gap:.5rem;">
                            <a href="{{ route('admin.social.channels.edit', $channel) }}" style="color:#3b82f6;text-decoration:none;font-size:.85rem;">Editar</a>
                            <form method="POST" action="{{ route('admin.social.channels.destroy', $channel) }}" onsubmit="return confirm('Excluir canal?')">
                                @csrf @method('DELETE')
                                <button style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.85rem;">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:2rem;text-align:center;color:#64748b;">Nenhum canal cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">{{ $channels->links() }}</div>
    </div>
</x-layouts.app>
