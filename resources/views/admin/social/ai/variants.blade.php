<x-layouts.app>
    <div style="padding:2rem;max-width:600px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Post</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#f1f5f9;margin-top:.5rem;">✨ Gerar Variações</h1>
            <p style="color:#94a3b8;">{{ $post->title }}</p>
        </div>

        @if(session('error'))
        <div style="background:#ef444420;border:1px solid #ef4444;color:#ef4444;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.social.ai.variants.store', $post) }}" style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:2rem;">
            @csrf
            <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Selecione as plataformas</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.5rem;">
                @foreach(['instagram'=>'📸 Instagram','facebook'=>'📘 Facebook','linkedin'=>'💼 LinkedIn','tiktok'=>'🎵 TikTok','threads'=>'🧵 Threads','youtube'=>'▶️ YouTube','pinterest'=>'📌 Pinterest'] as $v=>$l)
                <label style="display:flex;align-items:center;gap:.5rem;background:#0f172a;border:1px solid #334155;padding:.75rem;border-radius:.5rem;cursor:pointer;">
                    <input type="checkbox" name="platforms[]" value="{{ $v }}" @checked(in_array($v,['instagram','facebook','linkedin'])) style="accent-color:#8b5cf6;">
                    <span style="color:#f1f5f9;font-size:.9rem;">{{ $l }}</span>
                </label>
                @endforeach
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#8b5cf6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">✨ Gerar Variações</button>
                <a href="{{ route('admin.social.posts.show', $post) }}" style="background:#334155;color:#94a3b8;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
