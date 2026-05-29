<x-layouts.app>
    <div style="padding:2rem;max-width:780px;margin:0 auto;">

        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Posts</a>
            <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.5rem;">✨ Criar Post com IA</h1>
            <p style="color:#64748b;font-size:.9rem;margin-top:.25rem;">Preencha o briefing. Gemini gera a legenda e a imagem. Você revisa antes de publicar.</p>
        </div>

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.posts.ai-create.store') }}">
            @csrf

            {{-- Theme --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Tema do Post *</h3>
                <input type="text" name="theme" value="{{ old('theme') }}" required
                    placeholder="Ex: Como a IA pode dobrar os resultados de uma pequena empresa em 90 dias"
                    style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.95rem;color:#0f172a;box-sizing:border-box;">
                <p style="color:#94a3b8;font-size:.8rem;margin-top:.4rem;">Descreva claramente o assunto central do post.</p>
            </div>

            {{-- Objective + Audience --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Objetivo *</h3>
                    <select name="objective" required style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;">
                        <option value="authority" {{ old('objective','authority')==='authority'?'selected':'' }}>Autoridade</option>
                        <option value="awareness" {{ old('objective')==='awareness'?'selected':'' }}>Awareness</option>
                        <option value="engagement" {{ old('objective')==='engagement'?'selected':'' }}>Engajamento</option>
                        <option value="leads" {{ old('objective')==='leads'?'selected':'' }}>Captação de Leads</option>
                        <option value="sales" {{ old('objective')==='sales'?'selected':'' }}>Vendas</option>
                        <option value="relationship" {{ old('objective')==='relationship'?'selected':'' }}>Relacionamento</option>
                    </select>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Público-alvo</h3>
                    <input type="text" name="target_audience" value="{{ old('target_audience','empreendedores e gestores digitais') }}"
                        placeholder="Ex: donos de e-commerce, gestores de marketing"
                        style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>
            </div>

            {{-- Tone + CTA --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Tom de voz</h3>
                    <input type="text" name="tone" value="{{ old('tone','profissional, inspirador e direto') }}"
                        placeholder="Ex: profissional, inspirador"
                        style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">CTA desejado</h3>
                    <input type="text" name="desired_cta" value="{{ old('desired_cta') }}"
                        placeholder="Ex: Fale com a Lymity IA, Acesse o link na bio"
                        style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                </div>
            </div>

            {{-- Image prompt --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Instrução visual da imagem <span style="font-weight:400;text-transform:none;">(opcional)</span></h3>
                <textarea name="image_prompt" rows="3"
                    placeholder="Descreva o estilo visual desejado. Ex: fundo azul escuro, elementos de IA abstratos, visual premium..."
                    style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;resize:vertical;box-sizing:border-box;">{{ old('image_prompt') }}</textarea>
                <p style="color:#94a3b8;font-size:.8rem;margin-top:.4rem;">Se vazio, usa o prompt padrão da Lymity IA.</p>
            </div>

            {{-- Scheduled at + Generate image --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Data/hora sugerida</h3>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                        style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;color:#0f172a;box-sizing:border-box;">
                    <p style="color:#94a3b8;font-size:.8rem;margin-top:.4rem;">Agendamento final é feito após aprovação.</p>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;display:flex;flex-direction:column;justify-content:center;">
                    <label style="display:flex;align-items:center;gap:.75rem;cursor:pointer;">
                        <input type="checkbox" name="generate_image" value="1" {{ old('generate_image') ? 'checked' : '' }}
                            style="width:1.2rem;height:1.2rem;accent-color:#8b5cf6;cursor:pointer;">
                        <div>
                            <div style="color:#0f172a;font-size:.95rem;font-weight:600;">Gerar imagem com Gemini</div>
                            <div style="color:#94a3b8;font-size:.8rem;margin-top:.15rem;">Pode ser gerada depois na tela do post</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Info box --}}
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.5rem;padding:1rem;margin-bottom:1.5rem;">
                <p style="color:#1e40af;font-size:.85rem;margin:0;">
                    <strong>Fluxo seguro:</strong> O post é criado como rascunho. Gemini gera legenda e imagem.
                    Você revisa, edita se necessário, envia para aprovação, e só então agenda ou publica no Instagram @lymity.ia.
                    Nada publica automaticamente.
                </p>
            </div>

            <div style="display:flex;gap:1rem;">
                <button type="submit"
                    style="background:#8b5cf6;color:#fff;padding:.75rem 2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:1rem;font-weight:600;">
                    ✨ Criar Post com IA
                </button>
                <a href="{{ route('admin.social.posts.index') }}"
                    style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.75rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.95rem;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
