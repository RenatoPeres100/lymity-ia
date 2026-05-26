<x-layouts.app>
    <div style="padding:2rem;max-width:700px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.posts.show', $post) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Post</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">✨ Melhorar Post com IA</h1>
            <p style="color:#64748b;">{{ $post->title }}</p>
        </div>

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        @if($post->main_caption)
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
            <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Conteúdo Atual</h3>
            <p style="color:#334155;font-size:.9rem;line-height:1.7;white-space:pre-wrap;">{{ Str::limit($post->main_caption, 400) }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.ai.improve.store', $post) }}" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf
            <div style="margin-bottom:1.5rem;">
                <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Instrução para a IA *</label>
                <textarea name="instruction" rows="5" required placeholder="Ex: Deixe o tom mais descontraído, adicione mais emojis e finalize com uma pergunta para engajamento..." style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('instruction') }}</textarea>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#8b5cf6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">✨ Melhorar</button>
                <a href="{{ route('admin.social.posts.show', $post) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
