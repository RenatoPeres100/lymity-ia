<x-layouts.app>
    <div style="padding:2rem;max-width:800px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;">
            <div>
                <a href="{{ route('admin.social.briefs.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Briefs</a>
                <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.5rem;">{{ $brief->title }}</h1>
                <p style="color:#64748b;margin-top:.25rem;">{{ $brief->client?->name ?? 'Agência' }} · {{ $brief->status ?? 'draft' }}</p>
            </div>
            <div style="display:flex;gap:.5rem;">
                <a href="{{ route('admin.social.briefs.edit', $brief) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.5rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;">Editar</a>
                <form method="POST" action="{{ route('admin.social.briefs.destroy', $brief) }}" onsubmit="return confirm('Excluir brief?')">
                    @csrf @method('DELETE')
                    <button style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:.5rem .9rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;">Excluir</button>
                </form>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
            <div>
                @if($brief->instructions)
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Instruções</h3>
                    <p style="color:#334155;font-size:.9rem;line-height:1.7;white-space:pre-wrap;">{{ $brief->instructions }}</p>
                </div>
                @endif
                @if($brief->references)
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Referências</h3>
                    <p style="color:#475569;font-size:.9rem;line-height:1.7;white-space:pre-wrap;">{{ $brief->references }}</p>
                </div>
                @endif
            </div>
            <div>
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Detalhes</h3>
                    @foreach([
                        ['label'=>'Objetivo','value'=>$brief->objective ?? '—'],
                        ['label'=>'Formato','value'=>$brief->content_type ?? '—'],
                        ['label'=>'Tom','value'=>$brief->tone ?? '—'],
                        ['label'=>'Prazo','value'=>$brief->due_date ? \Carbon\Carbon::parse($brief->due_date)->format('d/m/Y') : '—'],
                        ['label'=>'Criado','value'=>$brief->created_at->format('d/m/Y')],
                    ] as $info)
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;font-size:.85rem;">{{ $info['label'] }}</span>
                        <span style="color:#334155;font-size:.85rem;">{{ $info['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
