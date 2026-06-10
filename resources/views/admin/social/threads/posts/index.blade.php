@extends('components.layouts.app')

@section('title', 'Posts Threads')

@section('content')
<div style="max-width:1000px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:1.5rem;">🧵</span>
            <div>
                <h1 style="font-size:1.4rem;font-weight:700;color:#0f172a;margin:0;">Posts Threads</h1>
                <p style="font-size:.85rem;color:#64748b;margin:.25rem 0 0;">Posts de texto para o canal Threads</p>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center;">
            @if($channel && $channel->isConnected())
                <span style="font-size:.75rem;color:#166534;background:#f0fdf4;border:1px solid #86efac;padding:.25rem .6rem;border-radius:.375rem;">
                    ● @{{ $channel->account_name ?? 'conectado' }}
                </span>
            @else
                <a href="{{ route('admin.social.threads.index') }}"
                   style="font-size:.75rem;color:#dc2626;background:#fef2f2;border:1px solid #fca5a5;padding:.25rem .6rem;border-radius:.375rem;text-decoration:none;">
                    ● Canal desconectado
                </a>
            @endif
            <a href="{{ route('admin.social.threads.posts.create') }}"
               style="background:#0f172a;color:#fff;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;font-weight:600;">
                + Novo Post
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <select name="status" style="border:1px solid #e2e8f0;border-radius:.375rem;padding:.4rem .75rem;font-size:.85rem;background:#fff;">
            <option value="">Todos os status</option>
            @foreach(['draft','pending_approval','approved','scheduled','publishing','published','failed','rejected'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" style="background:#2563eb;color:#fff;padding:.4rem .75rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Filtrar</button>
        @if(request('status'))
            <a href="{{ route('admin.social.threads.posts.index') }}" style="padding:.4rem .75rem;font-size:.85rem;color:#64748b;text-decoration:none;">Limpar</a>
        @endif
    </form>

    @if($posts->isEmpty())
        <div style="text-align:center;padding:3rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;">
            <div style="font-size:2.5rem;margin-bottom:.75rem;">🧵</div>
            <p style="color:#64748b;font-size:.9rem;">Nenhum post Threads encontrado.</p>
            <a href="{{ route('admin.social.threads.posts.create') }}"
               style="display:inline-block;margin-top:1rem;background:#0f172a;color:#fff;padding:.5rem 1rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;">
                Criar primeiro post
            </a>
        </div>
    @else
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;overflow:hidden;">
            @foreach($posts as $post)
            @php
                $statusColors = [
                    'draft'            => ['bg'=>'#f8fafc','text'=>'#475569','border'=>'#e2e8f0'],
                    'pending_approval' => ['bg'=>'#fffbeb','text'=>'#92400e','border'=>'#fde047'],
                    'approved'         => ['bg'=>'#eff6ff','text'=>'#1e40af','border'=>'#bfdbfe'],
                    'scheduled'        => ['bg'=>'#f0fdf4','text'=>'#166534','border'=>'#86efac'],
                    'publishing'       => ['bg'=>'#fff7ed','text'=>'#9a3412','border'=>'#fed7aa'],
                    'published'        => ['bg'=>'#f0fdf4','text'=>'#166534','border'=>'#86efac'],
                    'failed'           => ['bg'=>'#fef2f2','text'=>'#dc2626','border'=>'#fca5a5'],
                    'rejected'         => ['bg'=>'#fef2f2','text'=>'#dc2626','border'=>'#fca5a5'],
                ];
                $sc = $statusColors[$post->status] ?? $statusColors['draft'];
            @endphp
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;gap:1rem;align-items:flex-start;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;">
                        <span style="font-size:.75rem;background:{{ $sc['bg'] }};color:{{ $sc['text'] }};border:1px solid {{ $sc['border'] }};padding:.15rem .5rem;border-radius:.375rem;white-space:nowrap;">
                            {{ $post->status }}
                        </span>
                        <a href="{{ route('admin.social.threads.posts.show', $post) }}"
                           style="font-weight:600;color:#0f172a;text-decoration:none;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $post->title ?? 'Sem título' }}
                        </a>
                    </div>
                    <p style="color:#64748b;font-size:.8rem;margin:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                        {{ $post->main_caption }}
                    </p>
                </div>
                <div style="text-align:right;flex-shrink:0;font-size:.75rem;color:#94a3b8;">
                    @if($post->scheduled_at)
                        <div>📅 {{ $post->scheduled_at->format('d/m H:i') }}</div>
                    @endif
                    @if($post->published_at)
                        <div>✓ {{ $post->published_at->format('d/m H:i') }}</div>
                    @endif
                    <div>{{ $post->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:1rem;">{{ $posts->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
