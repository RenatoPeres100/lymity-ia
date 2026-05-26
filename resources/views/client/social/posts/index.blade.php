<x-layouts.app>
    <div style="padding:2rem;">
        <div style="margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Meus Posts</h1>
            <p style="color:#64748b;">Posts criados para sua marca</p>
        </div>

        <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;">
            <select name="status" style="background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem .75rem;border-radius:.375rem;font-size:.85rem;">
                <option value="">Todos</option>
                @foreach(['draft'=>'Rascunho','pending_approval'=>'Pendente','approved'=>'Aprovado','scheduled'=>'Agendado','published'=>'Publicado','rejected'=>'Rejeitado'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" style="background:#4f46e5;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Filtrar</button>
        </form>

        <div style="display:grid;gap:1rem;">
            @forelse($posts as $post)
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;display:flex;justify-content:space-between;align-items:flex-start;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                        <span style="font-size:.75rem;padding:.2rem .6rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">{{ $post->status_label }}</span>
                        <span style="color:#64748b;font-size:.8rem;">{{ $post->objective_label }} · {{ $post->content_type_label }}</span>
                    </div>
                    <h3 style="color:#0f172a;font-size:1rem;font-weight:600;margin-bottom:.5rem;">{{ $post->title }}</h3>
                    @if($post->main_caption)
                    <p style="color:#475569;font-size:.85rem;line-height:1.5;">{{ Str::limit($post->main_caption, 150) }}</p>
                    @endif
                    @if($post->scheduled_at)
                    <p style="color:#8b5cf6;font-size:.8rem;margin-top:.5rem;">Agendado: {{ $post->scheduled_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <a href="{{ route('client.social.posts.show', $post) }}" style="color:#4f46e5;text-decoration:none;font-size:.85rem;margin-left:1rem;white-space:nowrap;">Ver →</a>
            </div>
            @empty
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:3rem;text-align:center;">
                <p style="color:#64748b;">Nenhum post criado para sua conta ainda.</p>
            </div>
            @endforelse
        </div>
        <div style="margin-top:1rem;">{{ $posts->links() }}</div>
    </div>
</x-layouts.app>
