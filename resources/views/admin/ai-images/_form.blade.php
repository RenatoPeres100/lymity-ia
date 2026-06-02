{{-- Shared form partial for create and edit --}}
@php
    $gen         = $generation ?? null;
    $prefill     = $prefill ?? [];
    $val         = fn(string $k, $default = '') => old($k, $gen ? $gen->{$k} : ($prefill[$k] ?? $default));
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- Seção 1: Destino --}}
    <div style="grid-column:1/-1;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;padding-bottom:.75rem;border-bottom:1px solid #f1f5f9;">Destino e Tipo</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Canal *</label>
                <select name="channel" required style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                    @foreach(['blog'=>'Blog','social'=>'Social','general'=>'Geral'] as $v=>$l)
                    <option value="{{ $v }}" @selected($val('channel')===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Finalidade *</label>
                <select name="purpose" required style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                    @foreach([
                        'featured_image'    => 'Imagem de destaque',
                        'social_single'     => 'Post social único',
                        'social_carousel'   => 'Carrossel social',
                        'blog_cover'        => 'Capa do blog',
                        'blog_inline'       => 'Imagem inline (blog)',
                        'other'             => 'Outro',
                    ] as $v=>$l)
                    <option value="{{ $v }}" @selected($val('purpose')===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Tipo de geração *</label>
                <select name="generation_type" required id="generation_type" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                    <option value="single" @selected($val('generation_type','single')==='single')>Imagem Única</option>
                    <option value="carousel" @selected($val('generation_type')==='carousel')>Carrossel</option>
                </select>
            </div>
        </div>

        {{-- Carousel slides count --}}
        <div id="slides_section" style="margin-top:1rem;{{ $val('generation_type')==='carousel' ? '' : 'display:none;' }}">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Número de slides</label>
            <input type="number" name="slides_count" min="1" max="10" value="{{ $val('slides_count', 5) }}" style="width:100px;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;">
        </div>

        {{-- Optional related content --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem;">
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Vincular a post social (opcional)</label>
                <select name="social_link" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                    <option value="">— Nenhum —</option>
                    @foreach($socialPosts ?? [] as $sp)
                    <option value="{{ $sp->id }}" @selected($val('related_id')==$sp->id && $val('related_type')==='App\Models\SocialPost')>{{ Str::limit($sp->title, 50) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Vincular a artigo do blog (opcional)</label>
                <select name="blog_link" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                    <option value="">— Nenhum —</option>
                    @foreach($blogPosts ?? [] as $bp)
                    <option value="{{ $bp->id }}" @selected($val('related_id')==$bp->id && $val('related_type')==='App\Models\BlogPost')>{{ Str::limit($bp->title, 50) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <input type="hidden" name="related_type" id="related_type_input" value="{{ $val('related_type') }}">
        <input type="hidden" name="related_id"   id="related_id_input"   value="{{ $val('related_id') }}">
    </div>

    {{-- Seção 2: Contexto --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;padding-bottom:.75rem;border-bottom:1px solid #f1f5f9;">Contexto do Conteúdo</h3>

        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Título</label>
            <input type="text" name="title" value="{{ $val('title') }}" placeholder="Ex: 5 sinais de que sua empresa precisa de automação" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Assunto / Tema Central</label>
            <input type="text" name="subject" value="{{ $val('subject') }}" placeholder="Ex: Automação de marketing com IA" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Contexto detalhado (prompt)</label>
            <textarea name="prompt_context" rows="4" placeholder="Descreva o contexto do conteúdo, o que a imagem deve representar..." style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;resize:vertical;box-sizing:border-box;">{{ $val('prompt_context') }}</textarea>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Público-alvo</label>
            <input type="text" name="target_audience" value="{{ $val('target_audience') }}" placeholder="Ex: Gestores de empresas de médio porte" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Tom</label>
                <input type="text" name="tone" value="{{ $val('tone') }}" placeholder="Ex: Estratégico e sofisticado" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Estilo visual</label>
                <input type="text" name="visual_style" value="{{ $val('visual_style') }}" placeholder="Ex: Tecnológico premium" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
            </div>
        </div>
        <div style="margin-top:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Contexto da marca (opcional)</label>
            <textarea name="brand_context" rows="2" placeholder="Informações sobre a marca para guiar o estilo visual..." style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;resize:vertical;box-sizing:border-box;">{{ $val('brand_context') }}</textarea>
        </div>
    </div>

    {{-- Seção 3: Layout --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;padding-bottom:.75rem;border-bottom:1px solid #f1f5f9;">Layout e Composição</h3>

        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Proporção</label>
            <select name="aspect_ratio" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                <option value="1:1" @selected($val('aspect_ratio','1:1')==='1:1')>1:1 — Quadrado (Instagram feed)</option>
                <option value="4:5" @selected($val('aspect_ratio')==='4:5')>4:5 — Vertical (Instagram feed)</option>
                <option value="9:16" @selected($val('aspect_ratio')==='9:16')>9:16 — Stories/Reels</option>
                <option value="16:9" @selected($val('aspect_ratio')==='16:9')>16:9 — Horizontal (Blog/YouTube)</option>
                <option value="3:2" @selected($val('aspect_ratio')==='3:2')>3:2 — Paisagem</option>
            </select>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Área segura (overlay)</label>
            <select name="overlay_mode" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;background:#fff;color:#334155;">
                <option value="none" @selected($val('overlay_mode','none')==='none')>Nenhuma — Sem área reservada</option>
                <option value="safe_top" @selected($val('overlay_mode')==='safe_top')>Topo limpo — para texto sobreposto</option>
                <option value="safe_bottom" @selected($val('overlay_mode')==='safe_bottom')>Rodapé limpo — para texto sobreposto</option>
                <option value="safe_left" @selected($val('overlay_mode')==='safe_left')>Lado esquerdo limpo</option>
                <option value="safe_right" @selected($val('overlay_mode')==='safe_right')>Lado direito limpo</option>
                <option value="center_free" @selected($val('overlay_mode')==='center_free')>Centro livre — para texto central</option>
            </select>
        </div>

        <div>
            <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Texto sobreposto (opcional)</label>
            <input type="text" name="overlay_text" value="{{ $val('overlay_text') }}" placeholder="Ex: Automação que gera resultado" style="width:100%;border:1px solid #e2e8f0;border-radius:.375rem;padding:.5rem;box-sizing:border-box;">
        </div>

        @if(!config('ai-images.enabled') || !config('ai-images.gemini.api_key_configured'))
        <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.5rem;padding:1rem;margin-top:1.5rem;">
            <p style="font-size:.85rem;font-weight:600;color:#854d0e;margin:0 0 .3rem;">Gemini não configurado</p>
            <p style="font-size:.8rem;color:#854d0e;margin:0;">Configure GEMINI_API_KEY e GEMINI_IMAGE_ENABLED=true no .env para habilitar a geração de imagens.</p>
            <a href="{{ route('admin.ai-images.settings') }}" style="font-size:.8rem;color:#854d0e;font-weight:600;">Ver configurações →</a>
        </div>
        @endif
    </div>

    {{-- Seção 4: Ações --}}
    <div style="grid-column:1/-1;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 1rem;padding-bottom:.75rem;border-bottom:1px solid #f1f5f9;">Ação</h3>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <button type="submit" name="action" value="draft" style="background:#f1f5f9;color:#475569;padding:.6rem 1.2rem;border-radius:.5rem;border:1px solid #e2e8f0;cursor:pointer;font-size:.9rem;">
                Salvar como rascunho
            </button>
            @if(config('ai-images.enabled') && config('ai-images.gemini.api_key_configured'))
            <button type="submit" name="action" value="generate" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">
                ✨ Gerar agora (síncrono)
            </button>
            <button type="submit" name="action" value="queue" style="background:#4f46e5;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">
                Salvar e enfileirar geração
            </button>
            @else
            <button type="submit" name="action" value="draft" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;opacity:.5;cursor:not-allowed;" disabled title="Configure o Gemini antes de gerar">
                ✨ Gerar (Gemini não configurado)
            </button>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('generation_type').addEventListener('change', function() {
    document.getElementById('slides_section').style.display = this.value === 'carousel' ? 'block' : 'none';
});

// Sync related type/id from selects
document.querySelector('[name="social_link"]')?.addEventListener('change', function() {
    if (this.value) {
        document.getElementById('related_type_input').value = 'App\\Models\\SocialPost';
        document.getElementById('related_id_input').value   = this.value;
        document.querySelector('[name="blog_link"]').value  = '';
    } else if (!document.querySelector('[name="blog_link"]').value) {
        document.getElementById('related_type_input').value = '';
        document.getElementById('related_id_input').value   = '';
    }
});
document.querySelector('[name="blog_link"]')?.addEventListener('change', function() {
    if (this.value) {
        document.getElementById('related_type_input').value = 'App\\Models\\BlogPost';
        document.getElementById('related_id_input').value   = this.value;
        document.querySelector('[name="social_link"]').value = '';
    } else if (!document.querySelector('[name="social_link"]').value) {
        document.getElementById('related_type_input').value = '';
        document.getElementById('related_id_input').value   = '';
    }
});
</script>
