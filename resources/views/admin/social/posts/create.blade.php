<x-layouts.app>
    <div style="padding:2rem;max-width:800px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Novo Post Social</h1>
            <p style="color:#64748b;font-size:.85rem;margin-top:.25rem;">Crie o texto primeiro → depois gere ou adicione a imagem → aprovação obrigatória antes de publicar.</p>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.posts.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Texto do post --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Texto do Post</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Título *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Objetivo *</label>
                        <select name="objective" required style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                            <option value="authority"    @selected(old('objective')==='authority')>Autoridade</option>
                            <option value="engagement"   @selected(old('objective')==='engagement')>Engajamento</option>
                            <option value="leads"        @selected(old('objective')==='leads')>Leads</option>
                            <option value="sales"        @selected(old('objective')==='sales')>Vendas</option>
                            <option value="awareness"    @selected(old('objective')==='awareness')>Awareness</option>
                            <option value="relationship" @selected(old('objective')==='relationship')>Relacionamento</option>
                        </select>
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Formato *</label>
                        <select name="content_type" required style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                            <option value="feed"        @selected(old('content_type')==='feed')>Feed</option>
                            <option value="story"       @selected(old('content_type')==='story')>Story</option>
                            <option value="reels"       @selected(old('content_type')==='reels')>Reels</option>
                            <option value="carousel"    @selected(old('content_type')==='carousel')>Carrossel</option>
                            <option value="short_video" @selected(old('content_type')==='short_video')>Vídeo Curto</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Legenda principal</label>
                        <textarea name="main_caption" rows="5"
                                  style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('main_caption') }}</textarea>
                        <p style="color:#94a3b8;font-size:.78rem;margin-top:.3rem;">A imagem só pode ser gerada com IA após preencher a legenda.</p>
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Hashtags</label>
                        <input type="text" name="hashtags" value="{{ old('hashtags') }}" placeholder="#marketing #ia"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">CTA</label>
                        <input type="text" name="cta" value="{{ old('cta') }}" placeholder="Clique no link da bio"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Brief criativo</label>
                        <textarea name="creative_brief" rows="2"
                                  style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('creative_brief') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Imagem do post --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.5rem;">Imagem do Post</h3>
                <p style="color:#94a3b8;font-size:.78rem;margin-bottom:1rem;">Escolha uma opção. Imagem obrigatória antes de enviar para aprovação.</p>

                <div style="display:grid;gap:.75rem;">
                    {{-- Option 1: Upload manual --}}
                    <label style="display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;background:#f8fafc;">
                        <input type="radio" name="image_input_type" value="upload" id="img_upload"
                               @checked(old('image_input_type')==='upload')
                               style="accent-color:#3b82f6;margin-top:.2rem;">
                        <div style="flex:1;">
                            <div style="font-weight:600;color:#334155;font-size:.9rem;">📁 Upload manual</div>
                            <div style="color:#64748b;font-size:.8rem;margin-top:.25rem;">JPEG ou PNG, máximo 8MB</div>
                            <div id="upload_field" style="margin-top:.75rem;display:none;">
                                <input type="file" name="image_upload" accept="image/jpeg,image/png"
                                       style="width:100%;font-size:.85rem;">
                            </div>
                        </div>
                    </label>

                    {{-- Option 2: URL externa --}}
                    <label style="display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;background:#f8fafc;">
                        <input type="radio" name="image_input_type" value="url" id="img_url"
                               @checked(old('image_input_type')==='url')
                               style="accent-color:#3b82f6;margin-top:.2rem;">
                        <div style="flex:1;">
                            <div style="font-weight:600;color:#334155;font-size:.9rem;">🔗 URL pública HTTPS</div>
                            <div style="color:#64748b;font-size:.8rem;margin-top:.25rem;">Link direto para imagem JPEG/PNG pública</div>
                            <div id="url_field" style="margin-top:.75rem;display:none;">
                                <input type="url" name="external_image_url" value="{{ old('external_image_url') }}"
                                       placeholder="https://..." style="width:100%;padding:.5rem;border:1px solid #e2e8f0;border-radius:.375rem;font-size:.85rem;box-sizing:border-box;">
                            </div>
                        </div>
                    </label>

                    {{-- Option 3: Gerar com Gemini após salvar --}}
                    <label style="display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border:1px solid #ddd6fe;border-radius:.5rem;cursor:pointer;background:#faf5ff;">
                        <input type="radio" name="image_input_type" value="gemini" id="img_gemini"
                               @checked(old('image_input_type')==='gemini')
                               style="accent-color:#7c3aed;margin-top:.2rem;">
                        <div style="flex:1;">
                            <div style="font-weight:600;color:#5b21b6;font-size:.9rem;">✨ Gerar com IA após salvar</div>
                            <div style="color:#7c3aed;font-size:.8rem;margin-top:.25rem;">A imagem será gerada com Gemini usando o texto do post como contexto. Preencha a legenda primeiro.</div>
                        </div>
                    </label>

                    {{-- Option 4: Sem imagem agora --}}
                    <label style="display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;background:#f8fafc;">
                        <input type="radio" name="image_input_type" value="none" id="img_none"
                               @checked(old('image_input_type', 'none')==='none')
                               style="accent-color:#64748b;margin-top:.2rem;">
                        <div>
                            <div style="font-weight:600;color:#64748b;font-size:.9rem;">Sem imagem agora</div>
                            <div style="color:#94a3b8;font-size:.8rem;margin-top:.25rem;">Post ficará como rascunho. Adicione imagem depois para poder aprovar.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Configurações --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Configurações</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Cliente</label>
                        <select name="client_id" style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                            <option value="">— Agência —</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.4rem;">Agendamento</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                               style="width:100%;background:#fff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <input type="checkbox" name="requires_approval" value="1" id="ra"
                               @checked(old('requires_approval', true)) style="accent-color:#3b82f6;">
                        <label for="ra" style="color:#475569;font-size:.9rem;cursor:pointer;">Exige aprovação</label>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;font-weight:600;">
                    Criar Post
                </button>
                <a href="{{ route('admin.social.posts.index') }}"
                   style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
    document.querySelectorAll('input[name="image_input_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('upload_field').style.display = 'none';
            document.getElementById('url_field').style.display    = 'none';
            if (this.value === 'upload') document.getElementById('upload_field').style.display = 'block';
            if (this.value === 'url')    document.getElementById('url_field').style.display    = 'block';
        });
        if (radio.checked) radio.dispatchEvent(new Event('change'));
    });
    </script>
</x-layouts.app>
