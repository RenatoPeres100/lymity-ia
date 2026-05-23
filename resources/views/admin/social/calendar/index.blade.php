<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;">Calendário Editorial</h1>
                <p style="color:#94a3b8;">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</p>
            </div>
            <a href="{{ route('admin.social.calendar.create') }}" style="background:#3b82f6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">+ Novo Calendário</a>
        </div>

        {{-- Navigation --}}
        <form method="GET" style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1rem;margin-bottom:1.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
            @php $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth(); $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth(); @endphp
            <a href="?month={{ $prev->month }}&year={{ $prev->year }}" style="color:#94a3b8;padding:.5rem .75rem;background:#334155;border-radius:.375rem;text-decoration:none;">← Anterior</a>
            <select name="month" style="background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.5rem;border-radius:.375rem;">
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" @selected($m===$month)>{{ \Carbon\Carbon::createFromDate($year,$m,1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <input type="number" name="year" value="{{ $year }}" min="2024" max="2030" style="background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.5rem;border-radius:.375rem;width:80px;">
            <select name="client_id" style="background:#0f172a;border:1px solid #334155;color:#f1f5f9;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os clientes</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" @selected($clientId==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" style="background:#3b82f6;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;">Ver</button>
            <a href="?month={{ $next->month }}&year={{ $next->year }}" style="color:#94a3b8;padding:.5rem .75rem;background:#334155;border-radius:.375rem;text-decoration:none;margin-left:auto;">Próximo →</a>
        </form>

        {{-- Calendar Grid --}}
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden;margin-bottom:1.5rem;">
            <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid #334155;">
                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $day)
                <div style="padding:.75rem;text-align:center;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">{{ $day }}</div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:repeat(7,1fr);">
                @for($i = 0; $i < $calendar['startDay']; $i++)
                <div style="padding:.75rem;min-height:80px;border:1px solid #1e293b;background:#0f172a20;"></div>
                @endfor
                @foreach($calendar['grid'] as $day => $data)
                <div style="padding:.5rem;min-height:80px;border:1px solid #334155;vertical-align:top;">
                    <div style="color:{{ $day == now()->day && $month == now()->month && $year == now()->year ? '#3b82f6' : '#94a3b8' }};font-size:.85rem;font-weight:600;margin-bottom:.25rem;">{{ $day }}</div>
                    @foreach($data['posts'] as $post)
                    <a href="{{ route('admin.social.posts.show', $post) }}" style="display:block;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};font-size:.7rem;padding:.15rem .4rem;border-radius:.25rem;margin-bottom:.2rem;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $post->title }}">{{ Str::limit($post->title, 20) }}</a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        {{-- Saved Calendars --}}
        @if($savedCalendars->isNotEmpty())
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
            <h3 style="color:#f1f5f9;font-weight:600;margin-bottom:1rem;">Calendários Salvos deste Mês</h3>
            @foreach($savedCalendars as $cal)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #334155;">
                <div>
                    <span style="color:#f1f5f9;font-size:.9rem;">{{ $cal->client?->name ?? 'Agência' }}</span>
                    <span style="color:#64748b;font-size:.8rem;margin-left:.5rem;">{{ $cal->status }}</span>
                </div>
                <a href="{{ route('admin.social.calendar.show', $cal) }}" style="color:#3b82f6;font-size:.85rem;text-decoration:none;">Ver →</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-layouts.app>
