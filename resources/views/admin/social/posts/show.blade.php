<x-layouts.app>
    <div style="padding:2rem;max-width:900px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;">
            <div>
                <a href="{{ route('admin.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Posts</a>
                <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.5rem;">{{ $post->title }}</h1>
                <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem;">
                    <span style="font-size:.8rem;padding:.25rem .7rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};">{{ $post->status_label }}</span>
                    <span style="color:#64748b;font-size:.8rem;">{{ $post->objective_label }} · {{ $post->content_type_label }}</span>
                    @if($post->client)
                    <span style="color:#64748b;font-size:.8rem;">{{ $post->client->name }}</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                @if(in_array($post->status, ['draft','rejected']))
                <form method="POST" action="{{ route('admin.social.posts.send-approval', $post) }}" style="display:inline;">
                    @csrf
                    <button style="background:#f59e0b;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Enviar Aprovação</button>
                </form>
                @endif
                @if($post->status === 'pending_approval')
                <form method="POST" action="{{ route('admin.social.posts.approve', $post) }}" style="display:inline;">
                    @csrf
                    <button style="background:#10b981;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Aprovar</button>
                </form>
                <form method="POST" action="{{ route('admin.social.posts.reject', $post) }}" style="display:inline;">
                    @csrf
                    <button style="background:#ef4444;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Rejeitar</button>
                </form>
                @endif
                @if($post->status === 'approved')
                <form method="POST" action="{{ route('admin.social.posts.mark-published', $post) }}" style="display:inline;">
                    @csrf
                    <button style="background:#10b981;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Marcar Publicado</button>
                </form>
                @endif
                <a href="{{ route('admin.social.posts.edit', $post) }}" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.5rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;">Editar</a>
                <a href="{{ route('admin.social.ai.improve', $post) }}" style="background:#eff6ff;color:#4f46e5;border:1px solid #e0e7ff;padding:.5rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;">✨ Melhorar IA</a>
            </div>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
            <div>
                {{-- Caption --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Legenda Principal</h3>
                    @if($post->main_caption)
                    <p style="color:#334155;font-size:.95rem;line-height:1.7;white-space:pre-wrap;">{{ $post->main_caption }}</p>
                    @else
                    <p style="color:#94a3b8;font-size:.9rem;">Sem legenda gerada.</p>
                    @endif
                    @if($post->hashtags)
                    <p style="color:#3b82f6;font-size:.85rem;margin-top:1rem;">{{ $post->hashtags }}</p>
                    @endif
                    @if($post->cta)
                    <p style="color:#10b981;font-size:.85rem;margin-top:.5rem;">CTA: {{ $post->cta }}</p>
                    @endif
                </div>

                {{-- Variants --}}
                @if($post->variants->isNotEmpty())
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Variações por Plataforma</h3>
                    @foreach($post->variants as $variant)
                    <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem;margin-bottom:1rem;">
                        <p style="color:#0f172a;font-size:.9rem;font-weight:600;margin-bottom:.5rem;">{{ strtoupper($variant->platform) }}</p>
                        <p style="color:#475569;font-size:.85rem;line-height:1.6;white-space:pre-wrap;">{{ $variant->caption }}</p>
                        @if($variant->creative_notes)
                        <p style="color:#94a3b8;font-size:.75rem;margin-top:.5rem;font-style:italic;">{{ $variant->creative_notes }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Generate Variants --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Gerar Variações</h3>
                    <a href="{{ route('admin.social.ai.variants', $post) }}" style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;">✨ Gerar com IA</a>
                </div>
            </div>

            <div>
                {{-- Schedule --}}
                @if(in_array($post->status, ['approved','draft']))
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Agendar</h3>
                    <form method="POST" action="{{ route('admin.social.posts.schedule', $post) }}">
                        @csrf @method('PATCH')
                        <input type="datetime-local" name="scheduled_at" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;font-size:.85rem;margin-bottom:.75rem;">
                        <button type="submit" style="background:#8b5cf6;color:#fff;padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;width:100%;">Agendar</button>
                    </form>
                </div>
                @endif

                {{-- Info --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Informações</h3>
                    @foreach([
                        ['label'=>'Criado','value'=>$post->created_at->format('d/m/Y H:i')],
                        ['label'=>'Agendado','value'=>$post->scheduled_at?->format('d/m/Y H:i') ?? '—'],
                        ['label'=>'Publicado','value'=>$post->published_at?->format('d/m/Y H:i') ?? '—'],
                        ['label'=>'Funcionário IA','value'=>$post->aiEmployee?->name ?? '—'],
                        ['label'=>'Aprovação','value'=>$post->requires_approval ? 'Sim' : 'Não'],
                    ] as $info)
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;font-size:.85rem;">{{ $info['label'] }}</span>
                        <span style="color:#334155;font-size:.85rem;">{{ $info['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Brief --}}
                @if($post->creative_brief)
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Brief Criativo</h3>
                    <p style="color:#475569;font-size:.85rem;line-height:1.6;">{{ $post->creative_brief }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Delete --}}
        <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0;">
            <form method="POST" action="{{ route('admin.social.posts.destroy', $post) }}" onsubmit="return confirm('Excluir este post?')">
                @csrf @method('DELETE')
                <button style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:.5rem 1rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;">Excluir Post</button>
            </form>
        </div>
    </div>
</x-layouts.app>
