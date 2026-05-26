<x-layouts.app>
    <div style="padding:2rem;max-width:700px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.briefs.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Briefs</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Novo Brief</h1>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.briefs.store') }}" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Título *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Objetivo</label>
                    <select name="objective" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">—</option>
                        <option value="authority" @selected(old('objective')==='authority')>Autoridade</option>
                        <option value="engagement" @selected(old('objective')==='engagement')>Engajamento</option>
                        <option value="leads" @selected(old('objective')==='leads')>Leads</option>
                        <option value="sales" @selected(old('objective')==='sales')>Vendas</option>
                        <option value="awareness" @selected(old('objective')==='awareness')>Awareness</option>
                        <option value="relationship" @selected(old('objective')==='relationship')>Relacionamento</option>
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Formato</label>
                    <select name="content_type" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">—</option>
                        <option value="feed" @selected(old('content_type')==='feed')>Feed</option>
                        <option value="story" @selected(old('content_type')==='story')>Story</option>
                        <option value="reels" @selected(old('content_type')==='reels')>Reels</option>
                        <option value="carousel" @selected(old('content_type')==='carousel')>Carrossel</option>
                        <option value="short_video" @selected(old('content_type')==='short_video')>Vídeo Curto</option>
                        <option value="article" @selected(old('content_type')==='article')>Artigo</option>
                        <option value="thread" @selected(old('content_type')==='thread')>Thread</option>
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Cliente</label>
                    <select name="client_id" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">— Agência —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Tom de voz</label>
                    <input type="text" name="tone" value="{{ old('tone') }}" placeholder="Profissional, descontraído..." style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Prazo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Instruções</label>
                    <textarea name="instructions" rows="4" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('instructions') }}</textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Referências</label>
                    <textarea name="references" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('references') }}</textarea>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Criar Brief</button>
                <a href="{{ route('admin.social.briefs.index') }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
