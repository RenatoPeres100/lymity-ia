<x-layouts.app>
    <div style="padding:2rem;max-width:800px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;">
            <div>
                <a href="{{ route('admin.social.briefs.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Briefs</a>
                <h1 style="font-size:1.6rem;font-weight:700;color:#f1f5f9;margin-top:.5rem;">{{ $brief->title }}</h1>
                <p style="color:#94a3b8;margin-top:.25rem;">{{ $brief->client?->name ?? 'Agência' }} · {{ $brief->status ?? 'draft' }}</p>
            </div>
            <div style="display:flex;gap:.5rem;">
                <a href="{{ route('admin.social.briefs.edit', $brief) }}" style="background:#334155;color:#94a3b8;padding:.5rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;">Editar</a>
                <form method="POST" action="{{ route('admin.social.briefs.destroy', $brief) }}" onsubmit="return confirm('Excluir brief?')">
                    @csrf @method('DELETE')
                    <button style="background:#ef444420;color:#ef4444;border:1px solid #ef4444;padding:.5rem .9rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;">Excluir</button>
                </form>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
            <div>
                @if($brief->instructions)
                <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Instruções</h3>
                    <p style="color:#f1f5f9;font-size:.9rem;line-height:1.7;white-space:pre-wrap;">{{ $brief->instructions }}</p>
                </div>
                @endif
                @if($brief->references)
                <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Referências</h3>
                    <p style="color:#94a3b8;font-size:.9rem;line-height:1.7;white-space:pre-wrap;">{{ $brief->references }}</p>
                </div>
                @endif
            </div>
            <div>
                <div style="background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#94a3b8;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Detalhes</h3>
                    @foreach([
                        ['label'=>'Objetivo','value'=>$brief->objective ?? '—'],
                        ['label'=>'Formato','value'=>$brief->content_type ?? '—'],
                        ['label'=>'Tom','value'=>$brief->tone ?? '—'],
                        ['label'=>'Prazo','value'=>$brief->due_date ? \Carbon\Carbon::parse($brief->due_date)->format('d/m/Y') : '—'],
                        ['label'=>'Criado','value'=>$brief->created_at->format('d/m/Y')],
                    ] as $info)
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #334155;">
                        <span style="color:#64748b;font-size:.85rem;">{{ $info['label'] }}</span>
                        <span style="color:#94a3b8;font-size:.85rem;">{{ $info['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
