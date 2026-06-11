<x-layouts.app title="Criar Post Threads">
<div style="max-width:720px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;">
        <a href="{{ route('admin.social.threads.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Posts Threads</a>
    </div>

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;">
        <span style="font-size:1.5rem;">🧵</span>
        <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0;">Criar Post Threads</h1>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.5rem;padding:.75rem;margin-bottom:1.5rem;font-size:.82rem;color:#1d4ed8;">
        📝 Posts Threads são somente texto nesta fase. Não há campo de imagem.
        O post passará por aprovação antes de ser publicado.
    </div>

    <form method="POST" action="{{ route('admin.social.threads.posts.store') }}">
        @csrf

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1rem;">
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    Título interno (referência)
                </label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="Ex: Post sobre automação com IA"
                       style="width:100%;padding:.6rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    Texto do post <span style="color:#dc2626;">*</span>
                    <span style="font-weight:400;color:#94a3b8;">(10–1800 caracteres)</span>
                </label>
                <textarea name="main_caption" rows="7" required minlength="10" maxlength="1800"
                          placeholder="A automação com IA não substitui estratégia. Ela amplifica a operação quando existe processo, contexto e direção."
                          style="width:100%;padding:.6rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;resize:vertical;box-sizing:border-box;line-height:1.6;"
                          oninput="document.getElementById('char-count').textContent=this.value.length">{{ old('main_caption') }}</textarea>
                <div style="text-align:right;font-size:.75rem;color:#94a3b8;margin-top:.25rem;">
                    <span id="char-count">{{ strlen(old('main_caption','')) }}</span>/1800
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    CTA (opcional)
                </label>
                <input type="text" name="cta" value="{{ old('cta') }}"
                       placeholder="Ex: Conheça a Lymity IA."
                       style="width:100%;padding:.6rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
            </div>

            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    Hashtags (opcional)
                    <span style="font-weight:400;color:#94a3b8;">máx 5, separadas por espaço ou vírgula</span>
                </label>
                <input type="text" name="hashtags" value="{{ old('hashtags') }}"
                       placeholder="#IA #Automacao #Negocios"
                       style="width:100%;padding:.6rem .75rem;border:1px solid #e2e8f0;border-radius:.5rem;font-size:.9rem;box-sizing:border-box;">
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.social.threads.posts.index') }}"
               style="padding:.6rem 1.2rem;border:1px solid #e2e8f0;border-radius:.5rem;text-decoration:none;color:#475569;font-size:.9rem;">
                Cancelar
            </a>
            <button type="submit"
                    style="background:#0f172a;color:#fff;padding:.6rem 1.4rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;font-weight:600;">
                Criar Post
            </button>
        </div>
    </form>
</div>
</x-layouts.app>
