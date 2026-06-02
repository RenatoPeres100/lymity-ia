<x-layouts.app>
    <div style="padding:2rem;">
        <div style="margin-bottom:2rem;">
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Logs de Imagens IA</h1>
            <p style="color:#64748b;">Histórico de gerações e consumo de Gemini</p>
        </div>

        {{-- Filters --}}
        <form method="GET" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
            <select name="status" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os status</option>
                @foreach(['draft'=>'Rascunho','queued'=>'Enfileirado','generating'=>'Gerando','completed'=>'Concluído','failed'=>'Falha','archived'=>'Arquivado'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="channel" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os canais</option>
                @foreach(['blog'=>'Blog','social'=>'Social','general'=>'Geral'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('channel')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <input type="text" name="provider" value="{{ request('provider') }}" placeholder="Provider..." style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;width:120px;">
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
            <button type="submit" style="background:#4f46e5;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;">Filtrar</button>
            <a href="{{ route('admin.ai-images.logs') }}" style="color:#4f46e5;padding:.5rem;text-decoration:none;font-size:.85rem;">Limpar</a>
        </form>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        @foreach(['ID','Título','Canal','Tipo','Provider','Modelo','Status','Duração','Usuário','Data'] as $h)
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $statusColors = ['draft'=>'#475569','queued'=>'#92400e','generating'=>'#1d4ed8','completed'=>'#166534','failed'=>'#991b1b','archived'=>'#94a3b8'];
                        $statusBgs    = ['draft'=>'#f1f5f9','queued'=>'#fef3c7','generating'=>'#dbeafe','completed'=>'#dcfce7','failed'=>'#fee2e2','archived'=>'#f1f5f9'];
                        $statusLabels = ['draft'=>'Rascunho','queued'=>'Enfileirado','generating'=>'Gerando','completed'=>'OK','failed'=>'Erro','archived'=>'Arquivado'];
                    @endphp
                    <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding:.6rem 1rem;color:#94a3b8;font-size:.8rem;">
                            <a href="{{ route('admin.ai-images.show', $log) }}" style="color:#4f46e5;text-decoration:none;">#{{ $log->id }}</a>
                        </td>
                        <td style="padding:.6rem 1rem;color:#374151;font-size:.85rem;">{{ Str::limit($log->title ?? '—', 30) }}</td>
                        <td style="padding:.6rem 1rem;color:#64748b;font-size:.82rem;">{{ ucfirst($log->channel) }}</td>
                        <td style="padding:.6rem 1rem;color:#64748b;font-size:.82rem;">{{ $log->isCarousel() ? "Carrossel ({$log->slides_count})" : 'Única' }}</td>
                        <td style="padding:.6rem 1rem;color:#64748b;font-size:.82rem;">{{ $log->provider ?? '—' }}</td>
                        <td style="padding:.6rem 1rem;color:#64748b;font-size:.82rem;">{{ $log->model ? Str::limit($log->model, 25) : '—' }}</td>
                        <td style="padding:.6rem 1rem;">
                            <span style="padding:.2rem .5rem;border-radius:.375rem;font-size:.75rem;font-weight:600;background:{{ $statusBgs[$log->status] ?? '#f1f5f9' }};color:{{ $statusColors[$log->status] ?? '#475569' }};">{{ $statusLabels[$log->status] ?? $log->status }}</span>
                        </td>
                        <td style="padding:.6rem 1rem;color:#94a3b8;font-size:.82rem;">{{ $log->duration_ms ? round($log->duration_ms/1000,1).'s' : '—' }}</td>
                        <td style="padding:.6rem 1rem;color:#64748b;font-size:.82rem;">{{ $log->creator?->name ?? '—' }}</td>
                        <td style="padding:.6rem 1rem;color:#94a3b8;font-size:.8rem;white-space:nowrap;">{{ $log->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @if($log->isFailed() && $log->error_message)
                    <tr style="border-bottom:1px solid #e2e8f0;background:#fef9f9;">
                        <td colspan="10" style="padding:.5rem 1rem;">
                            <p style="font-size:.8rem;color:#991b1b;margin:0;">Erro: {{ Str::limit($log->error_message, 150) }}</p>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="10" style="padding:3rem;text-align:center;color:#94a3b8;">Nenhum log encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">{{ $logs->links() }}</div>
    </div>
</x-layouts.app>
