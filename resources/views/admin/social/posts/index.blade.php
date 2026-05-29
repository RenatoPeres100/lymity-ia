<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Posts Sociais</h1>
                <p style="color:#64748b;">{{ $posts->total() }} post(s) encontrado(s)</p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('admin.social.posts.ai-create') }}" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">✨ Criar com IA</a>
                <a href="{{ route('admin.social.posts.create') }}" style="background:#3b82f6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">+ Novo Manual</a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
            <select name="status" style="background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;min-width:140px;">
                <option value="">Todos os status</option>
                @foreach(['draft'=>'Rascunho','pending_approval'=>'Pendente','approved'=>'Aprovado','scheduled'=>'Agendado','published'=>'Publicado','rejected'=>'Rejeitado'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="client_id" style="background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;min-width:160px;">
                <option value="">Todos os clientes</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected(request('client_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" style="background:#4f46e5;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;">Filtrar</button>
            <a href="{{ route('admin.social.posts.index') }}" style="color:#4f46e5;padding:.5rem;text-decoration:none;align-self:center;font-size:.85rem;">Limpar</a>
        </form>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        @foreach(['Título','Objetivo','Tipo','Cliente','Status','Agendado','Ações'] as $h)
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding:.75rem 1rem;color:#0f172a;font-size:.9rem;">{{ Str::limit($post->title, 35) }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $post->objective_label }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $post->content_type_label }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.85rem;">{{ $post->client?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;">
                            <span style="font-size:.75rem;padding:.2rem .6rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">{{ $post->status_label }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.8rem;">{{ $post->scheduled_at?->format('d/m H:i') ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;">
                            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#3b82f6;text-decoration:none;font-size:.85rem;">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:2rem;text-align:center;color:#64748b;">Nenhum post encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $posts->links() }}</div>
    </div>
</x-layouts.app>
