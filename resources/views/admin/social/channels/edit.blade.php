<x-layouts.app>
    <div style="padding:2rem;max-width:600px;">
        <div style="margin-bottom:2rem;">
            <a href="{{ route('admin.social.channels.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Canais</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin-top:.5rem;">Editar Canal</h1>
        </div>

        <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.85rem;">
            ⚠️ Tokens de acesso são gerenciados de forma segura. Não são exibidos nem alteráveis por este formulário.
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.social.channels.update', $channel) }}" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;font-weight:600;">Plataforma</label>
                    <div style="background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">{{ $channel->platform_emoji }} {{ $channel->platform_label }}</div>
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;font-weight:600;">Nome</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $channel->account_name) }}" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;font-weight:600;">Handle / @</label>
                    <input type="text" name="handle" value="{{ old('handle', $channel->handle) }}" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                </div>
                <div>
                    <label style="color:#475569;font-size:.85rem;display:block;margin-bottom:.5rem;font-weight:600;">Status</label>
                    <select name="status" style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.6rem .9rem;border-radius:.5rem;font-size:.9rem;">
                        @foreach(['active'=>'Ativo','paused'=>'Pausado','disconnected'=>'Desconectado'] as $v=>$l)
                        <option value="{{ $v }}" @selected(old('status',$channel->status)===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:.7rem 1.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.9rem;">Salvar</button>
                <a href="{{ route('admin.social.channels.index') }}" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:.7rem 1.5rem;border-radius:.5rem;text-decoration:none;font-size:.9rem;">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
