<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;">Social Media</h1>
                <p style="color:#94a3b8;margin-top:.25rem;">Visão geral do módulo de redes sociais</p>
            </div>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('admin.social.ai.generate') }}" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">✨ Gerar com IA</a>
                <a href="{{ route('admin.social.posts.create') }}" style="background:#3b82f6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">+ Novo Post</a>
            </div>
        </div>

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
            @foreach([
                ['label'=>'Total de Posts','value'=>$stats['total_posts'],'color'=>'#3b82f6'],
                ['label'=>'Pendentes Aprovação','value'=>$stats['pending'],'color'=>'#f59e0b'],
                ['label'=>'Agendados','value'=>$stats['scheduled'],'color'=>'#8b5cf6'],
                ['label'=>'Publicados','value'=>$stats['published'],'color'=>'#10b981'],
                ['label'=>'Canais Ativos','value'=>$stats['channels'],'color'=>'#06b6d4'],
            ] as $s)
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.25rem;">
                <p style="color:#94a3b8;font-size:.8rem;margin-bottom:.5rem;">{{ $s['label'] }}</p>
                <p style="font-size:2rem;font-weight:700;color:{{ $s['color'] }};">{{ $s['value'] }}</p>
            </div>
            @endforeach
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            {{-- Recent Posts --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="color:#f1f5f9;font-weight:600;">Posts Recentes</h3>
                    <a href="{{ route('admin.social.posts.index') }}" style="color:#3b82f6;font-size:.85rem;text-decoration:none;">Ver todos →</a>
                </div>
                @forelse($recentPosts as $post)
                <div style="padding:.75rem 0;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="color:#f1f5f9;font-size:.9rem;font-weight:500;">{{ Str::limit($post->title, 40) }}</p>
                        <p style="color:#64748b;font-size:.75rem;">{{ $post->client?->name ?? 'Agência' }} · {{ $post->created_at->diffForHumans() }}</p>
                    </div>
                    <span style="font-size:.75rem;padding:.25rem .6rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">
                        {{ $post->status_label }}
                    </span>
                </div>
                @empty
                <p style="color:#64748b;font-size:.9rem;">Nenhum post criado ainda.</p>
                @endforelse
            </div>

            {{-- Upcoming --}}
            <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="color:#f1f5f9;font-weight:600;">Próximos Agendamentos</h3>
                    <a href="{{ route('admin.social.calendar.index') }}" style="color:#3b82f6;font-size:.85rem;text-decoration:none;">Calendário →</a>
                </div>
                @forelse($upcomingPosts as $post)
                <div style="padding:.75rem 0;border-bottom:1px solid #334155;">
                    <p style="color:#f1f5f9;font-size:.9rem;font-weight:500;">{{ Str::limit($post->title, 40) }}</p>
                    <p style="color:#64748b;font-size:.75rem;">{{ $post->scheduled_at?->format('d/m/Y H:i') }} · {{ $post->client?->name ?? 'Agência' }}</p>
                </div>
                @empty
                <p style="color:#64748b;font-size:.9rem;">Nenhum post agendado.</p>
                @endforelse
            </div>
        </div>

        {{-- Quick links --}}
        <div style="display:flex;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;">
            <a href="{{ route('admin.social.channels.index') }}" style="background:#1e293b;border:1px solid #334155;color:#94a3b8;padding:.75rem 1.25rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">📡 Canais</a>
            <a href="{{ route('admin.social.briefs.index') }}" style="background:#1e293b;border:1px solid #334155;color:#94a3b8;padding:.75rem 1.25rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">📋 Briefs</a>
            <a href="{{ route('admin.social.calendar.index') }}" style="background:#1e293b;border:1px solid #334155;color:#94a3b8;padding:.75rem 1.25rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">📅 Calendário</a>
        </div>
    </div>
</x-layouts.app>
