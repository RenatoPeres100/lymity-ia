<x-layouts.app>
    <div style="padding:2rem;max-width:860px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Editar Post</h1>
        </div>

        @foreach(['success','warning','error'] as $type)
            @if(session($type))
            <div style="background:{{ $type==='success'?'#f0fdf4':($type==='error'?'#fef2f2':'#fffbeb') }};border:1px solid {{ $type==='success'?'#86efac':($type==='error'?'#fca5a5':'#fcd34d') }};color:{{ $type==='success'?'#166534':($type==='error'?'#dc2626':'#92400e') }};padding:.75rem;border-radius:.5rem;margin-bottom:.75rem;">
                {{ session($type) }}
            </div>
            @endif
        @endforeach
        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
        @endif

        {{-- Image outdated alert --}}
        @if($post->isImageOutdated())
        <div style="background:#fff7ed;border:2px solid #f97316;border-radius:.75rem;padding:1rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <span style="color:#c2410c;font-weight:700;font-size:.9rem;">⚠ Imagem desatualizada</span>
                <p style="color:#c2410c;font-size:.85rem;margin-top:.25rem;">O texto foi alterado depois da geração da imagem. Regenere antes de aprovar.</p>
            </div>
            @if($post->canBeEdited())
            <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}"
                  onsubmit="return confirm('Regenerar imagem com base no texto atual?')">
                @csrf
                <button style="background:#f97316;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;white-space:nowrap;">
                    Regenerar Imagem
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Main form --}}
        <form method="POST" action="{{ route('admin.social.posts.update', $post) }}"
              enctype="multipart/form-data"
              style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf @method('PATCH')

            {{-- Texto --}}
            <div style="margin-bottom:1.75rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Texto do Post</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Título</label>
                        <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Objetivo</label>
                        <select name="objective" style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                            <option value="authority"    @selected(old('objective',$post->objective)==='authority')>Autoridade</option>
                            <option value="engagement"   @selected(old('objective',$post->objective)==='engagement')>Engajamento</option>
                            <option value="leads"        @selected(old('objective',$post->objective)==='leads')>Leads</option>
                            <option value="sales"        @selected(old('objective',$post->objective)==='sales')>Vendas</option>
                            <option value="awareness"    @selected(old('objective',$post->objective)==='awareness')>Awareness</option>
                            <option value="relationship" @selected(old('objective',$post->objective)==='relationship')>Relacionamento</option>
                        </select>
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Formato</label>
                        <select name="content_type" style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                            <option value="feed"        @selected(old('content_type',$post->content_type)==='feed')>Feed</option>
                            <option value="story"       @selected(old('content_type',$post->content_type)==='story')>Story</option>
                            <option value="reels"       @selected(old('content_type',$post->content_type)==='reels')>Reels</option>
                            <option value="carousel"    @selected(old('content_type',$post->content_type)==='carousel')>Carrossel</option>
                            <option value="short_video" @selected(old('content_type',$post->content_type)==='short_video')>Vídeo Curto</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Legenda principal</label>
                        <textarea name="main_caption" rows="5"
                                  style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('main_caption', $post->main_caption) }}</textarea>
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Hashtags</label>
                        <input type="text" name="hashtags" value="{{ old('hashtags', $post->hashtags) }}"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">CTA</label>
                        <input type="text" name="cta" value="{{ old('cta', $post->cta) }}"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Brief criativo</label>
                        <textarea name="creative_brief" rows="2"
                                  style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('creative_brief', $post->creative_brief) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Configurações --}}
            <div style="margin-bottom:1.75rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Configurações</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Data/hora agendada</label>
                        <input type="datetime-local" name="scheduled_at"
                               value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Formato criativo</label>
                        <input type="text" name="creative_format" value="{{ old('creative_format', $post->creative_format) }}"
                               placeholder="feed_image, carousel, etc."
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
            </div>

            {{-- Salvar texto --}}
            <div style="display:flex;gap:1rem;margin-bottom:2rem;padding-bottom:2rem;border-bottom:1px solid #f1f5f9;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;font-weight:600;">
                    Salvar Texto
                </button>
                <a href="{{ route('admin.social.posts.show', $post) }}"
                   style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">
                    Cancelar
                </a>
            </div>
        </form>

        {{-- Seção de Imagem (separada do form de texto) --}}
        @if($post->canBeEdited())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.75rem;margin-top:1.5rem;">
            <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Imagem do Post</h3>

            {{-- Preview atual --}}
            @if($post->public_image_url)
            <div style="margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                    <span style="color:#475569;font-size:.85rem;font-weight:600;">Imagem atual</span>
                    @php
                        $imgStatusColor = match($post->image_validation_status) {
                            'valid'   => '#166534',
                            'invalid' => '#dc2626',
                            default   => '#64748b',
                        };
                        $imgStatusLabel = match($post->image_status ?? '') {
                            'valid'      => 'Válida ✓',
                            'invalid'    => 'Inválida ✗',
                            'generating' => 'Gerando...',
                            'failed'     => 'Falhou',
                            'replaced'   => 'Substituída',
                            default      => ($post->image_status ?? '—'),
                        };
                    @endphp
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <span style="font-size:.78rem;padding:.2rem .5rem;border-radius:9999px;background:{{ $imgStatusColor }}20;color:{{ $imgStatusColor }};font-weight:600;">{{ $imgStatusLabel }}</span>
                        @if($post->isImageOutdated())
                        <span style="font-size:.75rem;padding:.2rem .5rem;border-radius:9999px;background:#fff7ed;color:#c2410c;font-weight:600;border:1px solid #f97316;">⚠ Desatualizada</span>
                        @endif
                    </div>
                </div>
                <img src="{{ $post->public_image_url }}" alt="Imagem atual"
                     style="max-width:300px;width:100%;border-radius:.5rem;border:1px solid #e2e8f0;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <div style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:.5rem;padding:.75rem;color:#dc2626;font-size:.8rem;">
                    Imagem não pôde ser carregada. URL: {{ Str::limit($post->public_image_url, 80) }}
                </div>
                @if($post->image_validation_status === 'invalid' && $post->image_validation_error)
                <p style="color:#dc2626;font-size:.8rem;margin-top:.5rem;"><strong>Erro:</strong> {{ $post->image_validation_error }}</p>
                @endif
                @if($post->image_last_generated_at)
                <p style="color:#94a3b8;font-size:.75rem;margin-top:.25rem;">Gerada {{ $post->image_last_generated_at->diffForHumans() }} · Modo: {{ $post->image_generation_mode ?? '—' }}</p>
                @endif
            </div>
            @else
            <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:.5rem;padding:2rem;text-align:center;margin-bottom:1.5rem;">
                <div style="color:#94a3b8;font-size:2.5rem;">🖼</div>
                <p style="color:#94a3b8;font-size:.9rem;margin-top:.5rem;">Sem imagem. Adicione uma das opções abaixo.</p>
            </div>
            @endif

            {{-- Ações de imagem --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- 1. Gerar com IA --}}
                @php $hasCaption = !empty(trim($post->main_caption ?? '')); @endphp
                <div style="border:1px solid {{ $hasCaption ? '#ddd6fe' : '#e2e8f0' }};border-radius:.5rem;padding:1rem;background:{{ $hasCaption ? '#faf5ff' : '#f8fafc' }};">
                    <div style="font-weight:600;color:{{ $hasCaption ? '#5b21b6' : '#94a3b8' }};font-size:.9rem;margin-bottom:.5rem;">
                        ✨ Gerar imagem com IA (Gemini)
                    </div>
                    @if($hasCaption)
                    <p style="color:#7c3aed;font-size:.8rem;margin-bottom:.75rem;">A imagem usará o texto atual do post como contexto.</p>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                        <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}"
                              onsubmit="return confirm('Gerar imagem com Gemini com base no texto atual?')">
                            @csrf
                            <button style="background:#7c3aed;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">
                                {{ $post->public_image_url ? 'Regenerar com IA' : 'Gerar com IA' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.social.posts.suggest-carousel', $post) }}">
                            @csrf
                            <button style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.5rem .9rem;border-radius:.375rem;border:1px solid #cbd5e1;cursor:pointer;font-size:.85rem;">
                                💡 Sugerir Carrossel
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.social.posts.generate-carousel', $post) }}"
                              onsubmit="return confirm('Gerar carrossel com IA? Isso criará múltiplos slides.')">
                            @csrf
                            <button style="background:#0ea5e9;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">
                                🎨 Gerar Carrossel
                            </button>
                        </form>
                    </div>
                    @else
                    <p style="color:#94a3b8;font-size:.85rem;font-style:italic;">Preencha a legenda do post antes de gerar imagem com IA.</p>
                    @endif
                </div>

                {{-- 2. Upload manual --}}
                <details style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                    <summary style="padding:.75rem 1rem;cursor:pointer;background:#f8fafc;font-size:.9rem;color:#334155;font-weight:600;list-style:none;display:flex;align-items:center;gap:.5rem;">
                        📁 Substituir por Upload (JPEG/PNG, máx 8MB)
                    </summary>
                    <form method="POST" action="{{ route('admin.social.posts.replace-image-upload', $post) }}"
                          enctype="multipart/form-data" style="padding:1rem;display:flex;gap:.75rem;align-items:center;">
                        @csrf
                        <input type="file" name="image" accept="image/jpeg,image/png" style="flex:1;font-size:.85rem;">
                        <button style="background:#475569;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;white-space:nowrap;">
                            Enviar imagem
                        </button>
                    </form>
                </details>

                {{-- 3. URL externa --}}
                <details style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                    <summary style="padding:.75rem 1rem;cursor:pointer;background:#f8fafc;font-size:.9rem;color:#334155;font-weight:600;list-style:none;display:flex;align-items:center;gap:.5rem;">
                        🔗 Substituir por URL HTTPS
                    </summary>
                    <form method="POST" action="{{ route('admin.social.posts.replace-image-url', $post) }}"
                          style="padding:1rem;display:flex;gap:.75rem;">
                        @csrf
                        <input type="url" name="public_image_url" placeholder="https://..."
                               style="flex:1;padding:.5rem;border:1px solid #e2e8f0;border-radius:.375rem;font-size:.85rem;">
                        <button style="background:#475569;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;white-space:nowrap;">
                            Definir URL
                        </button>
                    </form>
                </details>

                {{-- 4. Revalidar --}}
                @if($post->public_image_url)
                <form method="POST" action="{{ route('admin.social.posts.validate-image', $post) }}" style="display:inline;">
                    @csrf
                    <button style="background:#0ea5e9;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">
                        Revalidar Imagem
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        {{-- Carousel assets preview --}}
        @if($post->carousel_enabled && $post->assets->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.75rem;margin-top:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;">Slides do Carrossel</h3>
                <span style="font-size:.8rem;color:#64748b;">{{ $post->validAssets()->count() }}/{{ $post->assets->count() }} válidos</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.75rem;">
                @foreach($post->assets->sortBy('position') as $asset)
                <div style="border:1px solid {{ $asset->isValid() ? '#86efac' : '#fca5a5' }};border-radius:.5rem;overflow:hidden;background:#f8fafc;">
                    @if($asset->public_url && str_starts_with($asset->public_url, 'https://'))
                        <img src="{{ $asset->public_url }}" alt="Slide {{ $asset->position }}"
                             style="width:100%;aspect-ratio:1;object-fit:cover;" onerror="this.style.display='none'">
                    @endif
                    <div style="padding:.4rem;font-size:.72rem;text-align:center;color:{{ $asset->isValid() ? '#166534' : '#dc2626' }};">
                        Slide {{ $asset->position }} · {{ $asset->isValid() ? '✓' : '✗' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-layouts.app>
