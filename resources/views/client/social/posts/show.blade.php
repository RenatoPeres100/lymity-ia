<x-layouts.app>
    <div style="padding:2rem;max-width:800px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('client.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Meus Posts</a>
            <h1 style="font-size:1.6rem;font-weight:700;color:#f1f5f9;margin-top:.5rem;">{{ $post->title }}</h1>
            <div style="display:flex;gap:.75rem;margin-top:.5rem;">
                <span style="font-size:.8rem;padding:.25rem .7rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">{{ $post->status_label }}</span>
                <span style="color:#64748b;font-size:.8rem;">{{ $post->objective_label }} · {{ $post->content_type_label }}</span>
            </div>
        </div>

        @if($post->main_caption)
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
            <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Legenda</h3>
            <p style="color:#f1f5f9;font-size:.95rem;line-height:1.7;white-space:pre-wrap;">{{ $post->main_caption }}</p>
            @if($post->hashtags)
            <p style="color:#3b82f6;font-size:.85rem;margin-top:1rem;">{{ $post->hashtags }}</p>
            @endif
            @if($post->cta)
            <p style="color:#10b981;font-size:.85rem;margin-top:.5rem;">→ {{ $post->cta }}</p>
            @endif
        </div>
        @endif

        @if($post->variants->isNotEmpty())
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
            <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Versões por Plataforma</h3>
            @foreach($post->variants as $variant)
            <div style="border:1px solid #334155;border-radius:.5rem;padding:1rem;margin-bottom:.75rem;">
                <p style="color:#8b5cf6;font-size:.85rem;font-weight:600;margin-bottom:.5rem;">{{ strtoupper($variant->platform) }}</p>
                <p style="color:#94a3b8;font-size:.85rem;line-height:1.6;white-space:pre-wrap;">{{ $variant->caption }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
            <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Detalhes</h3>
            @foreach([
                ['label'=>'Criado','value'=>$post->created_at->format('d/m/Y')],
                ['label'=>'Agendado','value'=>$post->scheduled_at?->format('d/m/Y H:i') ?? '—'],
                ['label'=>'Publicado','value'=>$post->published_at?->format('d/m/Y H:i') ?? '—'],
            ] as $info)
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #334155;">
                <span style="color:#64748b;font-size:.85rem;">{{ $info['label'] }}</span>
                <span style="color:#94a3b8;font-size:.85rem;">{{ $info['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
