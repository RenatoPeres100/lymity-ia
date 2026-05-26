<x-layouts.app>
    <div style="padding:2rem;max-width:600px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Post</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">✨ Gerar Variações</h1>
            <p style="color:#64748b;">{{ $post->title }}</p>
        </div>

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.social.ai.variants.store', $post) }}" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf
            <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Selecione as plataformas</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.5rem;">
                @foreach(['instagram'=>'📸 Instagram','facebook'=>'📘 Facebook','linkedin'=>'💼 LinkedIn','tiktok'=>'🎵 TikTok','threads'=>'🧵 Threads','youtube'=>'▶️ YouTube','pinterest'=>'📌 Pinterest'] as $v=>$l)
                <label style="display:flex;align-items:center;gap:.5rem;background:#ffffff;border:1px solid #e2e8f0;padding:.75rem;border-radius:.5rem;cursor:pointer;">
                    <input type="checkbox" name="platforms[]" value="{{ $v }}" @checked(in_array($v,['instagram','facebook','linkedin'])) style="accent-color:#8b5cf6;">
                    <span style="color:#334155;font-size:.9rem;">{{ $l }}</span>
                </label>
                @endforeach
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#8b5cf6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">✨ Gerar Variações</button>
                <a href="{{ route('admin.social.posts.show', $post) }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
