<x-layouts.app>
    <div style="padding:2rem;max-width:800px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Editar Post</h1>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.posts.update', $post) }}" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Título</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Objetivo</label>
                    <select name="objective" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="authority" @selected(old('objective',$post->objective)==='authority')>Autoridade</option>
                        <option value="engagement" @selected(old('objective',$post->objective)==='engagement')>Engajamento</option>
                        <option value="leads" @selected(old('objective',$post->objective)==='leads')>Leads</option>
                        <option value="sales" @selected(old('objective',$post->objective)==='sales')>Vendas</option>
                        <option value="awareness" @selected(old('objective',$post->objective)==='awareness')>Awareness</option>
                        <option value="relationship" @selected(old('objective',$post->objective)==='relationship')>Relacionamento</option>
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Formato</label>
                    <select name="content_type" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="feed" @selected(old('content_type',$post->content_type)==='feed')>Feed</option>
                        <option value="story" @selected(old('content_type',$post->content_type)==='story')>Story</option>
                        <option value="reels" @selected(old('content_type',$post->content_type)==='reels')>Reels</option>
                        <option value="carousel" @selected(old('content_type',$post->content_type)==='carousel')>Carrossel</option>
                        <option value="short_video" @selected(old('content_type',$post->content_type)==='short_video')>Vídeo Curto</option>
                        <option value="article" @selected(old('content_type',$post->content_type)==='article')>Artigo</option>
                        <option value="thread" @selected(old('content_type',$post->content_type)==='thread')>Thread</option>
                    </select>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Legenda principal</label>
                    <textarea name="main_caption" rows="5" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('main_caption', $post->main_caption) }}</textarea>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Hashtags</label>
                    <input type="text" name="hashtags" value="{{ old('hashtags', $post->hashtags) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">CTA</label>
                    <input type="text" name="cta" value="{{ old('cta', $post->cta) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Brief criativo</label>
                    <textarea name="creative_brief" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('creative_brief', $post->creative_brief) }}</textarea>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Salvar</button>
                <a href="{{ route('admin.social.posts.show', $post) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
