<x-layouts.app>
    <div style="padding:2rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;">
            <div>
                <a href="{{ route('admin.ai-images.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Imagens IA</a>
                <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin:.5rem 0 .25rem;">{{ $generation->title ?? "Geração #{$generation->id}" }}</h1>
                <p style="color:#64748b;margin:0;">{{ $generation->subject }}</p>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                @if($generation->canRegenerate())
                <form action="{{ route('admin.ai-images.regenerate', $generation) }}" method="POST">
                    @csrf
                    <button type="submit" style="background:#8b5cf6;color:#fff;padding:.5rem 1rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Regenerar</button>
                </form>
                @endif
                <a href="{{ route('admin.ai-images.edit', $generation) }}" style="background:#f1f5f9;color:#475569;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;border:1px solid #e2e8f0;">Editar</a>
                @if($generation->status !== 'archived')
                <form action="{{ route('admin.ai-images.archive', $generation) }}" method="POST">
                    @csrf
                    <button type="submit" style="background:#fef2f2;color:#991b1b;padding:.5rem 1rem;border-radius:.5rem;border:1px solid #fca5a5;cursor:pointer;font-size:.9rem;" onclick="return confirm('Arquivar esta geração?')">Arquivar</button>
                </form>
                @endif
            </div>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

            {{-- Left: Images --}}
            <div>
                {{-- Status --}}
                @php
                    $statusColors = ['draft'=>'#475569','queued'=>'#92400e','generating'=>'#1d4ed8','completed'=>'#166534','failed'=>'#991b1b','archived'=>'#94a3b8'];
                    $statusBgs    = ['draft'=>'#f1f5f9','queued'=>'#fef3c7','generating'=>'#dbeafe','completed'=>'#dcfce7','failed'=>'#fee2e2','archived'=>'#f1f5f9'];
                    $statusLabels = ['draft'=>'Rascunho','queued'=>'Na fila','generating'=>'Gerando','completed'=>'Concluído','failed'=>'Falha','archived'=>'Arquivado'];
                @endphp
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;">
                    <span style="padding:.3rem .8rem;border-radius:.5rem;font-size:.85rem;font-weight:700;background:{{ $statusBgs[$generation->status] ?? '#f1f5f9' }};color:{{ $statusColors[$generation->status] ?? '#475569' }};">
                        {{ $statusLabels[$generation->status] ?? $generation->status }}
                    </span>
                    @if($generation->isFailed() && $generation->error_message)
                    <p style="color:#991b1b;font-size:.85rem;margin:0;">{{ $generation->error_message }}</p>
                    @endif
                    @if($generation->isCompleted())
                    <p style="color:#166534;font-size:.85rem;margin:0;">Gerado em {{ $generation->generated_at?->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($generation->status === 'generating')
                    <p style="color:#1d4ed8;font-size:.85rem;margin:0;animation:pulse 1.5s infinite;">Gerando imagens, aguarde...</p>
                    @endif
                    @if($generation->status === 'draft')
                    <form action="{{ route('admin.ai-images.generate', $generation) }}" method="POST">
                        @csrf
                        <button type="submit" style="background:#8b5cf6;color:#fff;padding:.4rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">✨ Gerar agora</button>
                    </form>
                    @endif
                </div>

                {{-- Images grid --}}
                @php $activeImages = $generation->images->where('status', 'active')->sortBy('slide_number'); @endphp
                @if($activeImages->count() > 0)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.25rem;">
                    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;">Imagens Geradas ({{ $activeImages->count() }})</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
                        @foreach($activeImages as $img)
                        <div style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                            <img src="{{ $img->public_url }}" alt="{{ $img->alt_text ?? 'Imagem gerada' }}" style="width:100%;height:180px;object-fit:cover;display:block;">
                            <div style="padding:.75rem;">
                                @if($generation->isCarousel())
                                <p style="font-size:.75rem;font-weight:600;color:#64748b;margin:0 0 .3rem;">Slide {{ $img->slide_number }}</p>
                                @endif
                                <p style="font-size:.75rem;color:#94a3b8;margin:0 0 .5rem;">{{ $img->mime_type }}</p>
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                    <a href="{{ $img->public_url }}" target="_blank" style="font-size:.75rem;color:#4f46e5;text-decoration:none;">Abrir</a>
                                    <button onclick="navigator.clipboard.writeText('{{ $img->public_url }}');alert('URL copiada!')" style="font-size:.75rem;color:#4f46e5;background:none;border:none;cursor:pointer;padding:0;">Copiar URL</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @elseif($generation->isCompleted())
                <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.75rem;padding:1rem;margin-bottom:1.25rem;">
                    <p style="color:#854d0e;font-size:.9rem;margin:0;">Geração concluída mas nenhuma imagem ativa encontrada. Tente regenerar.</p>
                </div>
                @endif

                {{-- Vincular --}}
                @if($generation->canAttach())
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.25rem;">
                    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;">Vincular a conteúdo</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <p style="font-size:.85rem;font-weight:600;color:#374151;margin:0 0 .5rem;">Vincular a post social</p>
                            <form action="" method="POST" id="attachSocialForm">
                                @csrf
                                <select id="attachSocialSelect" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;margin-bottom:.5rem;background:#fff;color:#334155;">
                                    <option value="">Selecione o post...</option>
                                    @foreach(\App\Models\SocialPost::whereIn('status',['draft','pending_approval','approved'])->latest()->limit(20)->get() as $sp)
                                    <option value="{{ $sp->id }}">{{ Str::limit($sp->title, 50) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="attachSocial()" style="background:#4f46e5;color:#fff;padding:.4rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Vincular</button>
                            </form>
                        </div>
                        <div>
                            <p style="font-size:.85rem;font-weight:600;color:#374151;margin:0 0 .5rem;">Vincular a artigo do blog</p>
                            <form action="" method="POST" id="attachBlogForm">
                                @csrf
                                <select id="attachBlogSelect" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;margin-bottom:.5rem;background:#fff;color:#334155;">
                                    <option value="">Selecione o artigo...</option>
                                    @foreach(\App\Models\BlogPost::whereIn('status',['draft','pending_approval','approved'])->latest()->limit(20)->get() as $bp)
                                    <option value="{{ $bp->id }}">{{ Str::limit($bp->title, 50) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="attachBlog()" style="background:#4f46e5;color:#fff;padding:.4rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Vincular</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Prompt --}}
                @if($generation->final_prompt)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                    <h3 style="font-size:.9rem;font-weight:700;color:#64748b;margin:0 0 .75rem;text-transform:uppercase;letter-spacing:.05em;">Prompt Final Enviado</h3>
                    <p style="font-size:.85rem;color:#475569;line-height:1.6;white-space:pre-wrap;margin:0;">{{ $generation->final_prompt }}</p>
                </div>
                @endif
            </div>

            {{-- Right: Details --}}
            <div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.25rem;">
                    <h3 style="font-size:.9rem;font-weight:700;color:#64748b;margin:0 0 .75rem;text-transform:uppercase;letter-spacing:.05em;">Detalhes</h3>
                    @php
                        $details = [
                            'Canal'          => ['blog'=>'Blog','social'=>'Social','general'=>'Geral'][$generation->channel] ?? $generation->channel,
                            'Finalidade'     => $generation->purpose,
                            'Tipo'           => $generation->isCarousel() ? "Carrossel ({$generation->slides_count} slides)" : 'Imagem única',
                            'Proporção'      => $generation->aspect_ratio ?? '—',
                            'Provider'       => $generation->provider ?? '—',
                            'Modelo'         => $generation->model ?? '—',
                            'Duração'        => $generation->duration_ms ? round($generation->duration_ms / 1000, 1) . 's' : '—',
                            'Criado por'     => $generation->creator?->name ?? '—',
                            'Criado em'      => $generation->created_at->format('d/m/Y H:i'),
                        ];
                    @endphp
                    @foreach($details as $k=>$v)
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:.82rem;color:#94a3b8;">{{ $k }}</span>
                        <span style="font-size:.82rem;color:#475569;font-weight:500;text-align:right;max-width:60%;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>

                @if($generation->prompt_context)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                    <h3 style="font-size:.9rem;font-weight:700;color:#64748b;margin:0 0 .75rem;text-transform:uppercase;letter-spacing:.05em;">Contexto Fornecido</h3>
                    <p style="font-size:.85rem;color:#475569;line-height:1.6;margin:0;">{{ $generation->prompt_context }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    function attachSocial() {
        const id = document.getElementById('attachSocialSelect').value;
        if (!id) { alert('Selecione um post social.'); return; }
        if (!confirm('Vincular esta geração ao post social selecionado?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/ai-images/{{ $generation->id }}/attach/social/' + id;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
    function attachBlog() {
        const id = document.getElementById('attachBlogSelect').value;
        if (!id) { alert('Selecione um artigo.'); return; }
        if (!confirm('Vincular esta imagem ao artigo selecionado?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/ai-images/{{ $generation->id }}/attach/blog/' + id;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</x-layouts.app>
