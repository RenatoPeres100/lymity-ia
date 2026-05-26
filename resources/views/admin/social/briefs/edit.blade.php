<x-layouts.app>
    <div style="padding:2rem;max-width:700px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.briefs.show', $brief) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Brief</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Editar Brief</h1>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.briefs.update', $brief) }}" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Título</label>
                    <input type="text" name="title" value="{{ old('title', $brief->title) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Objetivo</label>
                    <select name="objective" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="">—</option>
                        <option value="authority" @selected(old('objective',$brief->objective)==='authority')>Autoridade</option>
                        <option value="engagement" @selected(old('objective',$brief->objective)==='engagement')>Engajamento</option>
                        <option value="leads" @selected(old('objective',$brief->objective)==='leads')>Leads</option>
                        <option value="sales" @selected(old('objective',$brief->objective)==='sales')>Vendas</option>
                        <option value="awareness" @selected(old('objective',$brief->objective)==='awareness')>Awareness</option>
                        <option value="relationship" @selected(old('objective',$brief->objective)==='relationship')>Relacionamento</option>
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Status</label>
                    <select name="status" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        <option value="draft" @selected(old('status',$brief->status)==='draft')>Rascunho</option>
                        <option value="in_progress" @selected(old('status',$brief->status)==='in_progress')>Em andamento</option>
                        <option value="completed" @selected(old('status',$brief->status)==='completed')>Concluído</option>
                    </select>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Tom de voz</label>
                    <input type="text" name="tone" value="{{ old('tone', $brief->tone) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Prazo</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $brief->due_date?->format('Y-m-d')) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Instruções</label>
                    <textarea name="instructions" rows="4" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('instructions', $brief->instructions) }}</textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;">Referências</label>
                    <textarea name="references" rows="3" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;resize:vertical;">{{ old('references', $brief->references) }}</textarea>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Salvar</button>
                <a href="{{ route('admin.social.briefs.show', $brief) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
