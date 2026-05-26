<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Briefs de Conteúdo</h1>
            <a href="{{ route('admin.social.briefs.create') }}" style="background:#3b82f6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">+ Novo Brief</a>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        @foreach(['Título','Objetivo','Formato','Cliente','Status','Prazo','Ações'] as $h)
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($briefs as $brief)
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        <td style="padding:.75rem 1rem;color:#0f172a;font-size:.9rem;">{{ Str::limit($brief->title, 40) }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $brief->objective ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $brief->content_type ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $brief->client?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $brief->status ?? 'draft' }}</td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.8rem;">{{ $brief->due_date ? \Carbon\Carbon::parse($brief->due_date)->format('d/m/Y') : '—' }}</td>
                        <td style="padding:.75rem 1rem;display:flex;gap:.5rem;">
                            <a href="{{ route('admin.social.briefs.show', $brief) }}" style="color:#3b82f6;text-decoration:none;font-size:.85rem;">Ver</a>
                            <a href="{{ route('admin.social.briefs.edit', $brief) }}" style="color:#94a3b8;text-decoration:none;font-size:.85rem;">Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:2rem;text-align:center;color:#64748b;">Nenhum brief criado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">{{ $briefs->links() }}</div>
    </div>
</x-layouts.app>
