@extends('components.layouts.app')

@section('title', 'Post Threads — ' . ($post->title ?? 'Ver'))

@section('content')
@php
    $statusColors = [
        'draft'            => ['bg'=>'#f8fafc','text'=>'#475569','border'=>'#e2e8f0'],
        'pending_approval' => ['bg'=>'#fffbeb','text'=>'#92400e','border'=>'#fde047'],
        'approved'         => ['bg'=>'#eff6ff','text'=>'#1e40af','border'=>'#bfdbfe'],
        'scheduled'        => ['bg'=>'#f0fdf4','text'=>'#166534','border'=>'#86efac'],
        'publishing'       => ['bg'=>'#fff7ed','text'=>'#9a3412','border'=>'#fed7aa'],
        'published'        => ['bg'=>'#dcfce7','text'=>'#166534','border'=>'#86efac'],
        'failed'           => ['bg'=>'#fef2f2','text'=>'#dc2626','border'=>'#fca5a5'],
        'rejected'         => ['bg'=>'#fef2f2','text'=>'#dc2626','border'=>'#fca5a5'],
    ];
    $sc = $statusColors[$post->status] ?? $statusColors['draft'];
@endphp

<div style="max-width:860px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('admin.social.threads.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.85rem;">← Posts Threads</a>
    </div>

    {{-- Flash messages --}}
    @foreach(['success','warning','error','info'] as $type)
        @if(session($type))
        <div style="background:{{ $type==='success'?'#f0fdf4':($type==='error'?'#fef2f2':'#fffbeb') }};border:1px solid {{ $type==='success'?'#86efac':($type==='error'?'#fca5a5':'#fcd34d') }};color:{{ $type==='success'?'#166534':($type==='error'?'#dc2626':'#92400e') }};padding:.75rem;border-radius:.5rem;margin-bottom:.75rem;">
            {{ session($type) }}
        </div>
        @endif
    @endforeach
    @if($errors->has('threads'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:.75rem;">
            {{ $errors->first('threads') }}
        </div>
    @endif

    <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;">

        {{-- Main content --}}
        <div style="flex:1;min-width:0;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                    <span style="font-size:1.25rem;">🧵</span>
                    <h2 style="font-size:1.1rem;font-weight:700;color:#0f172a;margin:0;">{{ $post->title ?? 'Post Threads' }}</h2>
                    <span style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};padding:.2rem .6rem;border-radius:.375rem;font-size:.75rem;white-space:nowrap;">
                        {{ $post->status }}
                    </span>
                </div>

                <div style="background:#f8fafc;border-radius:.5rem;padding:1rem;margin-bottom:1rem;white-space:pre-wrap;font-size:.9rem;color:#0f172a;line-height:1.7;">{{ $post->main_caption }}</div>

                @if($post->cta)
                <div style="font-size:.85rem;color:#475569;margin-bottom:.5rem;"><strong>CTA:</strong> {{ $post->cta }}</div>
                @endif

                @if($post->hashtags)
                <div style="font-size:.82rem;color:#2563eb;">{{ $post->hashtags }}</div>
                @endif
            </div>

            {{-- Metadata --}}
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
                <h3 style="font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 .75rem;">Detalhes</h3>
                <div style="display:grid;gap:.4rem;font-size:.82rem;color:#475569;">
                    <div><strong>Platform:</strong> threads</div>
                    <div><strong>Tipo:</strong> threads_text</div>
                    <div><strong>Criado em:</strong> {{ $post->created_at->format('d/m/Y H:i') }}</div>
                    @if($post->approved_at)
                    <div><strong>Aprovado em:</strong> {{ $post->approved_at->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($post->scheduled_at)
                    <div><strong>Agendado para:</strong> {{ $post->scheduled_at->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($post->published_at)
                    <div><strong>Publicado em:</strong> {{ $post->published_at->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($post->external_post_id)
                    <div><strong>ID externo:</strong> {{ $post->external_post_id }}</div>
                    @endif
                    @if($post->publication_error)
                    <div style="color:#dc2626;"><strong>Erro:</strong> {{ $post->publication_error }}</div>
                    @endif
                    @if($post->creative_brief)
                    <div><strong>Ângulo estratégico:</strong> {{ $post->creative_brief }}</div>
                    @endif
                    @php $meta = $post->metadata ?? []; @endphp
                    @if(!empty($meta['approval_notes']))
                    <div><strong>Notas de aprovação:</strong> {{ $meta['approval_notes'] }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar actions --}}
        <div style="width:260px;flex-shrink:0;">

            {{-- Edit --}}
            @if(in_array($post->status, ['draft','rejected','pending_approval']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 .75rem;">Editar</h3>
                <a href="{{ route('admin.social.threads.posts.edit', $post) }}"
                   style="display:block;text-align:center;background:#f1f5f9;color:#374151;padding:.5rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;">
                    ✏️ Editar Post
                </a>
            </div>
            @endif

            {{-- Submit for approval --}}
            @if(in_array($post->status, ['draft','rejected']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 .75rem;">Aprovação</h3>
                <form method="POST" action="{{ route('admin.social.threads.posts.submit-approval', $post) }}">
                    @csrf
                    <button style="width:100%;background:#2563eb;color:#fff;padding:.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.85rem;font-weight:600;">
                        Enviar para Aprovação
                    </button>
                </form>
            </div>
            @endif

            {{-- Schedule --}}
            @if(in_array($post->status, ['approved']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 .75rem;">Agendamento</h3>
                <form method="POST" action="{{ route('admin.social.threads.posts.schedule', $post) }}">
                    @csrf
                    <input type="datetime-local" name="scheduled_at" required
                           min="{{ now()->addMinutes(2)->format('Y-m-d\TH:i') }}"
                           style="width:100%;padding:.5rem;border:1px solid #e2e8f0;border-radius:.375rem;font-size:.82rem;margin-bottom:.5rem;box-sizing:border-box;">
                    <button style="width:100%;background:#0f172a;color:#fff;padding:.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.85rem;font-weight:600;">
                        Agendar Publicação
                    </button>
                </form>
            </div>
            @endif

            {{-- Publish now --}}
            @php
                $canPublishNow = $canPublish['ready']
                    && $publishingEnabled
                    && $channel?->isConnected()
                    && $channel?->hasValidToken();
            @endphp
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;margin-bottom:1rem;">
                <h3 style="font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0 0 .75rem;">Publicação Threads</h3>
                @if($canPublishNow)
                    <form method="POST" action="{{ route('admin.social.threads.posts.publish-now', $post) }}"
                          onsubmit="return confirm('Publicar agora no Threads?')">
                        @csrf
                        <button style="width:100%;background:#000;color:#fff;padding:.5rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.85rem;font-weight:600;">
                            🧵 Publicar agora
                        </button>
                    </form>
                @else
                    <div style="font-size:.78rem;color:#94a3b8;line-height:1.8;">
                        @if(!in_array($post->status, ['approved','scheduled']))<div>• Status: {{ $post->status }} (precisa ser aprovado)</div>@endif
                        @if($post->requires_approval && empty($post->approved_by))<div>• Aprovação pendente</div>@endif
                        @if(!$publishingEnabled)<div>• THREADS_PUBLISHING_ENABLED=false</div>@endif
                        @if(!$channel)<div>• Canal Threads não encontrado</div>
                        @elseif(!$channel->isConnected())<div>• Canal não conectado</div>@endif
                        @if(!empty($canPublish['reasons']))
                            @foreach($canPublish['reasons'] as $reason)<div>• {{ $reason }}</div>@endforeach
                        @endif
                    </div>
                @endif
                @if($post->status === 'published')
                    <div style="color:#166534;font-size:.82rem;font-weight:600;text-align:center;margin-top:.5rem;">✓ Publicado no Threads</div>
                @endif
            </div>

            {{-- Readiness check --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;font-size:.78rem;color:#475569;">
                <div style="font-weight:700;margin-bottom:.5rem;">Checklist</div>
                <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.25rem;">
                    @if($post->platform === 'threads')<span style="color:#16a34a;">✓</span>@else<span style="color:#dc2626;">✗</span>@endif
                    platform=threads
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.25rem;">
                    @if($post->content_type === 'threads_text')<span style="color:#16a34a;">✓</span>@else<span style="color:#dc2626;">✗</span>@endif
                    content_type=threads_text
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.25rem;">
                    @if(!empty(trim($post->main_caption ?? '')))<span style="color:#16a34a;">✓</span>@else<span style="color:#dc2626;">✗</span>@endif
                    Texto presente
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.25rem;">
                    @if(!empty($post->approved_by))<span style="color:#16a34a;">✓</span>@else<span style="color:#94a3b8;">○</span>@endif
                    Aprovado
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    @if($channel?->isConnected())<span style="color:#16a34a;">✓</span>@else<span style="color:#dc2626;">✗</span>@endif
                    Canal conectado
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
