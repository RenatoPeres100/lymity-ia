<x-layouts.app>
<div style="padding:2rem;max-width:820px;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
        <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.4rem;">Editar Post</h1>
    </div>

    {{-- Alertas de sessão --}}
    @foreach(['success','warning','error'] as $t)
        @if(session($t))
        <div style="background:{{ $t==='success'?'#f0fdf4':($t==='error'?'#fef2f2':'#fffbeb') }};border:1px solid {{ $t==='success'?'#86efac':($t==='error'?'#fca5a5':'#fcd34d') }};color:{{ $t==='success'?'#166534':($t==='error'?'#dc2626':'#92400e') }};padding:.75rem;border-radius:.5rem;margin-bottom:.75rem;">
            {{ session($t) }}
        </div>
        @endif
    @endforeach
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:.75rem;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- Alerta imagem desatualizada --}}
    @if($post->isImageOutdated())
    <div style="background:#fff7ed;border:2px solid #f97316;border-radius:.6rem;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <span style="color:#c2410c;font-weight:700;">⚠ Imagem desatualizada</span>
            <p style="color:#c2410c;font-size:.85rem;margin-top:.2rem;">O texto foi alterado depois da geração da imagem. Regenere antes de aprovar ou publicar.</p>
        </div>
        @if($post->canBeEdited())
        <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}"
              onsubmit="return confirm('Regenerar imagem com base no texto atual?')">
            @csrf
            <button style="background:#f97316;color:#fff;padding:.5rem 1.1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;white-space:nowrap;">
                ↻ Regenerar Imagem
            </button>
        </form>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- FORM: salvar texto + agendamento                    --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('admin.social.posts.update', $post) }}"
          style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.25rem;">
        @csrf @method('PATCH')

        <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1.25rem;">Texto do Post</h2>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div style="grid-column:1/-1;">
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Título</label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}" required
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Objetivo</label>
                <select name="objective" style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                    @foreach(['authority'=>'Autoridade','engagement'=>'Engajamento','leads'=>'Leads','sales'=>'Vendas','awareness'=>'Awareness','relationship'=>'Relacionamento'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('objective',$post->objective)===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Formato</label>
                <select name="content_type" style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                    @foreach(['feed'=>'Feed','reels'=>'Reels','story'=>'Story','carousel'=>'Carrossel','short_video'=>'Vídeo Curto'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('content_type',$post->content_type)===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div style="grid-column:1/-1;">
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Legenda principal</label>
                <textarea name="main_caption" rows="5"
                          style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;resize:vertical;box-sizing:border-box;">{{ old('main_caption', $post->main_caption) }}</textarea>
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Hashtags</label>
                <input type="text" name="hashtags" value="{{ old('hashtags', $post->hashtags) }}"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">CTA</label>
                <input type="text" name="cta" value="{{ old('cta', $post->cta) }}"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>

            <div style="grid-column:1/-1;">
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Brief criativo</label>
                <textarea name="creative_brief" rows="2"
                          style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;resize:vertical;box-sizing:border-box;">{{ old('creative_brief', $post->creative_brief) }}</textarea>
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Agendamento</label>
                <input type="datetime-local" name="scheduled_at"
                       value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>

            <div>
                <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Formato criativo</label>
                <input type="text" name="creative_format" value="{{ old('creative_format', $post->creative_format) }}"
                       placeholder="feed_image, carousel, etc."
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
            <button type="submit"
                    style="background:#3b82f6;color:#fff;padding:.65rem 1.5rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.9rem;font-weight:600;">
                Salvar Texto
            </button>
            <a href="{{ route('admin.social.posts.show', $post) }}"
               style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.65rem 1.1rem;border-radius:.4rem;text-decoration:none;font-size:.9rem;display:inline-flex;align-items:center;">
                Cancelar
            </a>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SEÇÃO DE IMAGEM — sempre visível                   --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div style="background:#fff;border:2px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.25rem;">
        <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1.25rem;">Imagem do Post</h2>

        {{-- Preview atual --}}
        @if($post->public_image_url)
        <div style="display:grid;grid-template-columns:200px 1fr;gap:1.25rem;margin-bottom:1.5rem;align-items:start;">
            <div>
                <img src="{{ $post->public_image_url }}" alt="Imagem atual"
                     style="width:100%;border-radius:.5rem;border:1px solid #e2e8f0;aspect-ratio:1;object-fit:cover;"
                     onerror="this.style.display='none';document.getElementById('img_error').style.display='block'">
                <div id="img_error" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:.4rem;padding:.5rem;font-size:.75rem;color:#dc2626;">Não carregou</div>
            </div>
            <div>
                @php
                    $vs = $post->image_validation_status;
                    $is = $post->image_status ?? '';
                    $statusColor = $vs === 'valid' ? '#166534' : ($vs === 'invalid' ? '#dc2626' : '#64748b');
                    $statusLabel = match($is) {
                        'valid'      => '✓ Válida',
                        'invalid'    => '✗ Inválida',
                        'generating' => '⏳ Gerando...',
                        'failed'     => '✗ Falhou',
                        'replaced'   => '↺ Substituída',
                        default      => ucfirst($is ?: '—'),
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem;">
                    <span style="font-size:.8rem;padding:.25rem .6rem;border-radius:9999px;background:{{ $statusColor }}15;color:{{ $statusColor }};font-weight:700;border:1px solid {{ $statusColor }}30;">
                        {{ $statusLabel }}
                    </span>
                    @if($post->isImageOutdated())
                    <span style="font-size:.75rem;padding:.25rem .6rem;border-radius:9999px;background:#fff7ed;color:#c2410c;font-weight:700;border:1px solid #f97316;">⚠ Desatualizada</span>
                    @endif
                    @if($post->image_generation_mode)
                    <span style="font-size:.75rem;color:#94a3b8;padding:.2rem .5rem;background:#f1f5f9;border-radius:.25rem;">{{ $post->image_generation_mode }}</span>
                    @endif
                </div>
                @if($post->image_last_generated_at)
                <p style="font-size:.78rem;color:#94a3b8;margin-bottom:.4rem;">Gerada {{ $post->image_last_generated_at->diffForHumans() }}</p>
                @endif
                @if($post->image_validation_status === 'invalid' && $post->image_validation_error)
                <p style="font-size:.8rem;color:#dc2626;background:#fef2f2;border:1px solid #fca5a5;border-radius:.35rem;padding:.5rem .75rem;margin-bottom:.5rem;">
                    <strong>Erro:</strong> {{ $post->image_validation_error }}
                </p>
                @endif
                <p style="font-size:.75rem;color:#94a3b8;word-break:break-all;">
                    <a href="{{ $post->public_image_url }}" target="_blank" style="color:#3b82f6;">{{ Str::limit($post->public_image_url, 70) }}</a>
                </p>

                {{-- Revalidar --}}
                <form method="POST" action="{{ route('admin.social.posts.validate-image', $post) }}" style="display:inline;margin-top:.5rem;">
                    @csrf
                    <button style="background:#0ea5e9;color:#fff;padding:.4rem .85rem;border-radius:.35rem;border:none;cursor:pointer;font-size:.8rem;">
                        ↻ Revalidar Imagem
                    </button>
                </form>
            </div>
        </div>
        @else
        <div style="background:#f8fafc;border:2px dashed #cbd5e1;border-radius:.5rem;padding:2.5rem;text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">🖼</div>
            <p style="color:#94a3b8;font-size:.9rem;">Nenhuma imagem definida.</p>
            <p style="color:#cbd5e1;font-size:.8rem;margin-top:.25rem;">Use uma das opções abaixo para adicionar.</p>
        </div>
        @endif

        {{-- ────────────────────────────────────────────── --}}
        {{-- OPÇÃO 1: Gerar com Gemini                      --}}
        {{-- ────────────────────────────────────────────── --}}
        @php $hasCaption = !empty(trim($post->main_caption ?? '')); @endphp
        <div style="border:2px solid {{ $hasCaption ? '#ddd6fe' : '#e2e8f0' }};border-radius:.6rem;padding:1.1rem;margin-bottom:.85rem;background:{{ $hasCaption ? '#faf5ff' : '#f8fafc' }};">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;">
                <span style="font-size:1.2rem;">✨</span>
                <span style="font-weight:700;color:{{ $hasCaption ? '#5b21b6' : '#94a3b8' }};font-size:.95rem;">Gerar com IA — Gemini</span>
            </div>

            @if($hasCaption)
            <p style="font-size:.82rem;color:#7c3aed;margin-bottom:.9rem;">
                A IA criará uma imagem baseada no texto, legenda, objetivo e CTA do post.<br>
                @if($post->public_image_url) A imagem atual será <strong>substituída</strong>. @endif
            </p>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}"
                      onsubmit="return confirm('Gerar imagem com Gemini com base no texto atual do post?')">
                    @csrf
                    <button style="background:#7c3aed;color:#fff;padding:.55rem 1.1rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.88rem;font-weight:600;">
                        ✨ {{ $post->public_image_url ? 'Regenerar com Gemini' : 'Gerar com Gemini' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.social.posts.generate-carousel', $post) }}"
                      onsubmit="return confirm('Gerar carrossel de imagens com IA? Isso criará vários slides.')">
                    @csrf
                    <button style="background:#0ea5e9;color:#fff;padding:.55rem 1.1rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.88rem;">
                        🎨 Gerar Carrossel IA
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.social.posts.suggest-carousel', $post) }}">
                    @csrf
                    <button style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;padding:.55rem 1rem;border-radius:.4rem;cursor:pointer;font-size:.85rem;">
                        💡 Verificar se carrossel faz sentido
                    </button>
                </form>
            </div>
            @else
            <p style="color:#94a3b8;font-size:.85rem;font-style:italic;">
                Preencha a legenda principal do post e salve antes de gerar a imagem com IA.
            </p>
            @endif
        </div>

        {{-- ────────────────────────────────────────────── --}}
        {{-- OPÇÃO 2: Upload Manual                         --}}
        {{-- ────────────────────────────────────────────── --}}
        <div style="border:1px solid #e2e8f0;border-radius:.6rem;padding:1.1rem;margin-bottom:.85rem;background:#f8fafc;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
                <span style="font-size:1.2rem;">📁</span>
                <span style="font-weight:700;color:#334155;font-size:.95rem;">Upload Manual</span>
                <span style="font-size:.75rem;color:#94a3b8;">JPEG ou PNG · máx 8 MB · mín 320×320 px</span>
            </div>
            <form method="POST" action="{{ route('admin.social.posts.replace-image-upload', $post) }}"
                  enctype="multipart/form-data">
                @csrf
                <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
                    <input type="file" name="image" accept="image/jpeg,image/png"
                           style="flex:1;min-width:200px;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.85rem;background:#fff;">
                    <button style="background:#475569;color:#fff;padding:.55rem 1.1rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.88rem;font-weight:600;white-space:nowrap;">
                        Enviar Imagem
                    </button>
                </div>
            </form>
        </div>

        {{-- ────────────────────────────────────────────── --}}
        {{-- OPÇÃO 3: URL Pública                           --}}
        {{-- ────────────────────────────────────────────── --}}
        <div style="border:1px solid #e2e8f0;border-radius:.6rem;padding:1.1rem;background:#f8fafc;">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
                <span style="font-size:1.2rem;">🔗</span>
                <span style="font-weight:700;color:#334155;font-size:.95rem;">URL Pública HTTPS</span>
                <span style="font-size:.75rem;color:#94a3b8;">Link direto para arquivo JPEG ou PNG</span>
            </div>
            <form method="POST" action="{{ route('admin.social.posts.replace-image-url', $post) }}">
                @csrf
                <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
                    <input type="url" name="public_image_url"
                           value="{{ $post->image_generation_mode === 'external_url' ? $post->public_image_url : '' }}"
                           placeholder="https://..."
                           style="flex:1;min-width:200px;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                    <button style="background:#475569;color:#fff;padding:.55rem 1.1rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.88rem;font-weight:600;white-space:nowrap;">
                        Definir URL
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SLIDES DO CARROSSEL (se houver)                    --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($post->carousel_enabled && $post->assets->isNotEmpty())
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Slides do Carrossel</h2>
            <span style="font-size:.8rem;color:#64748b;">{{ $post->validAssets()->count() }} / {{ $post->assets->count() }} válidos</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;">
            @foreach($post->assets->sortBy('position') as $asset)
            <div style="border:2px solid {{ $asset->isValid() ? '#86efac' : '#fca5a5' }};border-radius:.5rem;overflow:hidden;">
                @if($asset->public_url && str_starts_with($asset->public_url, 'https://'))
                <img src="{{ $asset->public_url }}" alt="Slide {{ $asset->position }}"
                     style="width:100%;aspect-ratio:1;object-fit:cover;display:block;"
                     onerror="this.style.display='none'">
                @endif
                <div style="padding:.4rem .5rem;font-size:.75rem;text-align:center;
                            background:{{ $asset->isValid() ? '#f0fdf4' : '#fef2f2' }};
                            color:{{ $asset->isValid() ? '#166534' : '#dc2626' }};font-weight:600;">
                    Slide {{ $asset->position }} · {{ $asset->isValid() ? '✓ OK' : '✗ '.ucfirst($asset->status) }}
                </div>
                @if($asset->validation_error)
                <div style="padding:.3rem .5rem;font-size:.7rem;color:#dc2626;background:#fef2f2;">{{ Str::limit($asset->validation_error, 50) }}</div>
                @endif
            </div>
            @endforeach
        </div>
        <div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.social.posts.generate-carousel', $post) }}"
                  onsubmit="return confirm('Regenerar todos os slides? Os slides atuais serão substituídos.')">
                @csrf
                <button style="background:#0ea5e9;color:#fff;padding:.5rem 1rem;border-radius:.4rem;border:none;cursor:pointer;font-size:.85rem;">
                    ↻ Regenerar Carrossel
                </button>
            </form>
        </div>
    </div>
    @endif

</div>
</x-layouts.app>
