<x-layouts.app>
<div style="padding:2rem;max-width:820px;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Posts</a>
        <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.4rem;">Novo Post Social</h1>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.25rem;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif
    @if(session('warning'))
    <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:.75rem;border-radius:.5rem;margin-bottom:1.25rem;">{{ session('warning') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.social.posts.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- ── TEXTO ── --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.25rem;">
            <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1.25rem;">① Texto do Post</h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Objetivo *</label>
                    <select name="objective" required style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                        @foreach(['authority'=>'Autoridade','engagement'=>'Engajamento','leads'=>'Leads','sales'=>'Vendas','awareness'=>'Awareness','relationship'=>'Relacionamento'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('objective')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Formato *</label>
                    <select name="content_type" required style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                        @foreach(['feed'=>'Feed','reels'=>'Reels','story'=>'Story','carousel'=>'Carrossel','short_video'=>'Vídeo Curto'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('content_type')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="grid-column:1/-1;">
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Legenda principal</label>
                    <textarea name="main_caption" rows="5" id="main_caption_input"
                              style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;resize:vertical;box-sizing:border-box;">{{ old('main_caption') }}</textarea>
                </div>

                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Hashtags</label>
                    <input type="text" name="hashtags" value="{{ old('hashtags') }}" placeholder="#lymityia #ia"
                           style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">CTA</label>
                    <input type="text" name="cta" value="{{ old('cta') }}" placeholder="Conheça a Lymity IA"
                           style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>

                <div style="grid-column:1/-1;">
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Brief criativo</label>
                    <textarea name="creative_brief" rows="2"
                              style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;resize:vertical;box-sizing:border-box;">{{ old('creative_brief') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── IMAGEM ── --}}
        <div style="background:#fff;border:2px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.25rem;">
            <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">② Imagem do Post</h2>
            <p style="font-size:.8rem;color:#94a3b8;margin-bottom:1.25rem;">Escolha uma opção. Você pode adicionar a imagem agora ou depois de salvar.</p>

            {{-- Opção A: Upload Manual --}}
            <div style="border:1px solid #e2e8f0;border-radius:.6rem;padding:1.1rem;margin-bottom:.75rem;background:#f8fafc;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
                    <span style="font-size:1.1rem;">📁</span>
                    <span style="font-weight:600;color:#334155;font-size:.9rem;">Upload Manual</span>
                    <span style="font-size:.75rem;color:#94a3b8;">JPEG ou PNG · máx 8 MB · mín 320×320 px</span>
                </div>
                <input type="file" name="image_upload" accept="image/jpeg,image/png"
                       style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.85rem;background:#fff;box-sizing:border-box;">
                <input type="hidden" name="image_input_type" id="image_input_type_hidden" value="{{ old('image_input_type','none') }}">
            </div>

            {{-- Opção B: URL Pública --}}
            <div style="border:1px solid #e2e8f0;border-radius:.6rem;padding:1.1rem;margin-bottom:.75rem;background:#f8fafc;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
                    <span style="font-size:1.1rem;">🔗</span>
                    <span style="font-weight:600;color:#334155;font-size:.9rem;">URL Pública (HTTPS)</span>
                    <span style="font-size:.75rem;color:#94a3b8;">Link direto para JPEG ou PNG</span>
                </div>
                <input type="url" name="external_image_url" value="{{ old('external_image_url') }}"
                       placeholder="https://ia.lymity.com.br/storage/social/generated/..."
                       style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
            </div>

            {{-- Opção C: Gemini IA --}}
            <div style="border:2px solid #ddd6fe;border-radius:.6rem;padding:1.1rem;background:#faf5ff;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                    <span style="font-size:1.1rem;">✨</span>
                    <span style="font-weight:600;color:#5b21b6;font-size:.9rem;">Gerar Imagem com IA — Gemini</span>
                </div>
                <p style="font-size:.8rem;color:#7c3aed;margin-bottom:.85rem;">
                    A IA usará o título, legenda, objetivo e CTA do post para criar uma imagem coerente.<br>
                    <strong>Preencha a legenda antes de marcar esta opção.</strong>
                </p>
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
                    <input type="checkbox" name="generate_with_gemini" value="1" id="generate_gemini_cb"
                           @checked(old('generate_with_gemini'))
                           style="width:1.1rem;height:1.1rem;accent-color:#7c3aed;cursor:pointer;">
                    <span style="font-size:.9rem;color:#4c1d95;font-weight:600;">Gerar imagem com Gemini após criar o post</span>
                </label>
            </div>
        </div>

        {{-- ── CONFIGURAÇÕES ── --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.25rem;">
            <h2 style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;">③ Configurações</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Cliente</label>
                    <select name="client_id" style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;">
                        <option value="">— Agência —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:.82rem;color:#475569;display:block;margin-bottom:.35rem;">Agendamento (opcional)</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                           style="width:100%;padding:.6rem .85rem;border:1px solid #cbd5e1;border-radius:.4rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <input type="checkbox" name="requires_approval" value="1" id="ra"
                           @checked(old('requires_approval',true)) style="accent-color:#3b82f6;width:1rem;height:1rem;">
                    <label for="ra" style="font-size:.88rem;color:#475569;cursor:pointer;">Exige aprovação antes de publicar</label>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit"
                    style="background:#3b82f6;color:#fff;padding:.7rem 1.75rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.95rem;font-weight:600;">
                Criar Post
            </button>
            <a href="{{ route('admin.social.posts.index') }}"
               style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.25rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;display:inline-flex;align-items:center;">
                Cancelar
            </a>
        </div>
    </form>
</div>
</x-layouts.app>
