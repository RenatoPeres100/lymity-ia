<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Calendário de Conteúdo</h1>
                <p style="color:#64748b;">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
            @php $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth(); $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth(); @endphp
            <a href="?month={{ $prev->month }}&year={{ $prev->year }}" style="color:#475569;padding:.5rem .75rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.375rem;text-decoration:none;">← Anterior</a>
            <select name="month" style="background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected($m===$month)>{{ \Carbon\Carbon::createFromDate($year,$m,1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ $year }}" min="2024" style="background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;width:80px;">
            <button type="submit" style="background:#3b82f6;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Ver</button>
            <a href="?month={{ $next->month }}&year={{ $next->year }}" style="color:#475569;padding:.5rem .75rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.375rem;text-decoration:none;margin-left:auto;">Próximo →</a>
        </form>

        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid #e2e8f0;">
                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d)
                <div style="padding:.75rem;text-align:center;color:#64748b;font-size:.8rem;font-weight:600;">{{ $d }}</div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:repeat(7,1fr);">
                @for($i = 0; $i < $calendar['startDay']; $i++)
                <div style="padding:.75rem;min-height:80px;background:#f8fafc;border:1px solid #f1f5f9;"></div>
                @endfor
                @foreach($calendar['grid'] as $day => $data)
                <div style="padding:.5rem;min-height:80px;border:1px solid #e2e8f0;">
                    <div style="color:{{ $day == now()->day && $month == now()->month && $year == now()->year ? '#3b82f6' : '#64748b' }};font-size:.85rem;font-weight:600;margin-bottom:.25rem;">{{ $day }}</div>
                    @foreach($data['posts'] as $post)
                    <a href="{{ route('client.social.posts.show', $post) }}" style="display:block;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};font-size:.7rem;padding:.15rem .4rem;border-radius:.25rem;margin-bottom:.2rem;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $post->title }}">{{ Str::limit($post->title, 18) }}</a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
