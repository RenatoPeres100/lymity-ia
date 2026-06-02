<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;">Imagens IA</h1>
                <p style="color:#64748b;">Studio de geração de imagens com Gemini AI</p>
            </div>
            <a href="{{ route('admin.ai-images.create') }}" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;display:flex;align-items:center;gap:.4rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nova Imagem
            </a>
        </div>

        {{-- Summary cards --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                <p style="color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;margin:0 0 .3rem;">Geradas Hoje</p>
                <p style="font-size:2rem;font-weight:700;color:#4f46e5;margin:0;">{{ $stats['today'] }}</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                <p style="color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;margin:0 0 .3rem;">Carrosséis Hoje</p>
                <p style="font-size:2rem;font-weight:700;color:#8b5cf6;margin:0;">{{ $stats['carousels'] }}</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                <p style="color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;margin:0 0 .3rem;">Falhas Hoje</p>
                <p style="font-size:2rem;font-weight:700;color:#ef4444;margin:0;">{{ $stats['failed'] }}</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                <p style="color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;margin:0 0 .3rem;">Status Gemini</p>
                @if(config('ai-images.enabled') && config('ai-images.gemini.api_key_configured'))
                    <p style="font-size:.9rem;font-weight:600;color:#16a34a;margin:0;">Configurado</p>
                @else
                    <p style="font-size:.9rem;font-weight:600;color:#dc2626;margin:0;">Não configurado</p>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
            <select name="channel" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os canais</option>
                @foreach(['blog'=>'Blog','social'=>'Social','general'=>'Geral'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('channel')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="type" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os tipos</option>
                <option value="single" @selected(request('type')==='single')>Imagem Única</option>
                <option value="carousel" @selected(request('type')==='carousel')>Carrossel</option>
            </select>
            <select name="status" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;">
                <option value="">Todos os status</option>
                @foreach(['draft'=>'Rascunho','queued'=>'Na fila','generating'=>'Gerando','completed'=>'Concluído','failed'=>'Falha','archived'=>'Arquivado'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;" placeholder="De">
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;" placeholder="Até">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar título..." style="background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;min-width:200px;">
            <button type="submit" style="background:#4f46e5;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;">Filtrar</button>
            <a href="{{ route('admin.ai-images.index') }}" style="color:#4f46e5;padding:.5rem;text-decoration:none;font-size:.85rem;">Limpar</a>
        </form>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Preview</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Título</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Canal</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Tipo</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Status</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Criado por</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Data</th>
                        <th style="text-align:left;padding:.75rem 1rem;color:#64748b;font-size:.8rem;font-weight:600;text-transform:uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($generations as $gen)
                    @php
                        $mainImg = $gen->activeImages->first();
                        $statusColors = [
                            'draft'      => 'background:#f1f5f9;color:#475569',
                            'queued'     => 'background:#fef3c7;color:#92400e',
                            'generating' => 'background:#dbeafe;color:#1d4ed8',
                            'completed'  => 'background:#dcfce7;color:#166534',
                            'failed'     => 'background:#fee2e2;color:#991b1b',
                            'archived'   => 'background:#f1f5f9;color:#94a3b8',
                        ];
                        $statusLabels = [
                            'draft'      => 'Rascunho',
                            'queued'     => 'Na fila',
                            'generating' => 'Gerando',
                            'completed'  => 'Concluído',
                            'failed'     => 'Falha',
                            'archived'   => 'Arquivado',
                        ];
                        $statusStyle = $statusColors[$gen->status] ?? 'background:#f1f5f9;color:#475569';
                    @endphp
                    <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding:.75rem 1rem;">
                            @if($mainImg)
                            <img src="{{ $mainImg->public_url }}" alt="preview" style="width:48px;height:48px;object-fit:cover;border-radius:.375rem;border:1px solid #e2e8f0;">
                            @else
                            <div style="width:48px;height:48px;background:#f1f5f9;border-radius:.375rem;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:.7rem;">sem img</div>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;color:#0f172a;font-size:.9rem;max-width:200px;">
                            <a href="{{ route('admin.ai-images.show', $gen) }}" style="color:#4f46e5;text-decoration:none;font-weight:500;">{{ Str::limit($gen->title ?? 'Sem título', 40) }}</a>
                            @if($gen->subject)<p style="margin:.2rem 0 0;color:#94a3b8;font-size:.8rem;">{{ Str::limit($gen->subject, 40) }}</p>@endif
                        </td>
                        <td style="padding:.75rem 1rem;">
                            @php $channelLabels = ['blog'=>'Blog','social'=>'Social','general'=>'Geral']; @endphp
                            <span style="font-size:.8rem;color:#64748b;">{{ $channelLabels[$gen->channel] ?? $gen->channel }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;">
                            <span style="font-size:.8rem;{{ $gen->isCarousel() ? 'color:#8b5cf6' : 'color:#64748b' }}">{{ $gen->isCarousel() ? "Carrossel ({$gen->slides_count})" : 'Única' }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;">
                            <span style="padding:.2rem .6rem;border-radius:.375rem;font-size:.75rem;font-weight:600;{{ $statusStyle }}">{{ $statusLabels[$gen->status] ?? $gen->status }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;color:#64748b;font-size:.85rem;">{{ $gen->creator?->name ?? '—' }}</td>
                        <td style="padding:.75rem 1rem;color:#94a3b8;font-size:.8rem;">{{ $gen->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding:.75rem 1rem;">
                            <div style="display:flex;gap:.5rem;">
                                <a href="{{ route('admin.ai-images.show', $gen) }}" style="background:#f1f5f9;color:#475569;padding:.3rem .6rem;border-radius:.375rem;text-decoration:none;font-size:.8rem;">Ver</a>
                                @if($gen->canRegenerate())
                                <form action="{{ route('admin.ai-images.generate', $gen) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" style="background:#8b5cf6;color:#fff;padding:.3rem .6rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;">Gerar</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding:3rem;text-align:center;color:#94a3b8;">
                            Nenhuma geração encontrada. <a href="{{ route('admin.ai-images.create') }}" style="color:#4f46e5;">Criar primeira imagem</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            {{ $generations->links() }}
        </div>
    </div>
</x-layouts.app>
