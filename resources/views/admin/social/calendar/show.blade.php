<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;">
            <div>
                <a href="{{ route('admin.social.calendar.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Calendários</a>
                <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;margin-top:.5rem;">
                    {{ \Carbon\Carbon::createFromDate($calendar->year, $calendar->month, 1)->translatedFormat('F Y') }}
                </h1>
                <p style="color:#94a3b8;">{{ $calendar->client?->name ?? 'Agência' }} · {{ $calendar->status }}</p>
            </div>
            <form method="POST" action="{{ route('admin.social.calendar.destroy', $calendar) }}" onsubmit="return confirm('Excluir calendário?')">
                @csrf @method('DELETE')
                <button style="background:#ef444420;color:#ef4444;border:1px solid #ef4444;padding:.5rem 1rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;">Excluir</button>
            </form>
        </div>

        @if($calendar->strategy_summary)
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
            <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Resumo Estratégico</h3>
            <p style="color:#f1f5f9;font-size:.9rem;line-height:1.7;">{{ $calendar->strategy_summary }}</p>
        </div>
        @endif

        {{-- Grid --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden;">
            <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid #334155;">
                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $day)
                <div style="padding:.75rem;text-align:center;color:#64748b;font-size:.8rem;font-weight:600;">{{ $day }}</div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:repeat(7,1fr);">
                @for($i = 0; $i < $grid['startDay']; $i++)
                <div style="padding:.75rem;min-height:80px;background:#0f172a20;border:1px solid #1a2332;"></div>
                @endfor
                @foreach($grid['grid'] as $day => $data)
                <div style="padding:.5rem;min-height:80px;border:1px solid #334155;">
                    <div style="color:#94a3b8;font-size:.85rem;font-weight:600;margin-bottom:.25rem;">{{ $day }}</div>
                    @foreach($data['posts'] as $post)
                    <a href="{{ route('admin.social.posts.show', $post) }}" style="display:block;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};font-size:.7rem;padding:.15rem .4rem;border-radius:.25rem;margin-bottom:.2rem;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($post->title, 18) }}</a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        {{-- Posts list --}}
        @if($posts->isNotEmpty())
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-top:1.5rem;">
            <h3 style="color:#f1f5f9;font-weight:600;margin-bottom:1rem;">Posts do Mês ({{ $posts->count() }})</h3>
            @foreach($posts as $post)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #334155;">
                <div>
                    <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#f1f5f9;text-decoration:none;font-size:.9rem;">{{ $post->title }}</a>
                    <span style="color:#64748b;font-size:.8rem;margin-left:.5rem;">{{ $post->scheduled_at?->format('d/m H:i') }}</span>
                </div>
                <span style="font-size:.75rem;padding:.2rem .6rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">{{ $post->status_label }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts.app>
