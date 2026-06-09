<x-layouts.app title="Aprovação #{{ $approvalRequest->id }}">

@php
    $approval  = $approvalRequest;
    $content   = $display['content']   ?? [];
    $visual    = $display['visual']    ?? [];
    $sources   = $display['sources']   ?? [];
    $ctxData   = $display['ctx_data']  ?? [];
    $usage     = $display['usage']     ?? [];
    $debug     = $display['debug']     ?? [];
    $package   = $display['package']   ?? null;
    $entity    = $display['entity']    ?? null;
    $task      = $display['task']      ?? null;
    $employee  = $display['employee']  ?? null;
    $run       = $display['run']       ?? null;

    $entityType  = $entity ? class_basename(get_class($entity)) : null;
    $isBlog      = $content['type'] === 'blog_post';
    $isSocial    = in_array($content['type'] ?? '', ['social_post','carousel','reel','story']);
    $isCarousel  = ($content['type'] ?? '') === 'carousel';
    $hasImage    = !empty($visual['has_image']);
    $imageAlert  = $task?->requires_image && !$hasImage;
@endphp

{{-- Top bar --}}
<div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <a href="{{ route('admin.approvals.index') }}" style="color:#4f46e5;font-size:.8rem;text-decoration:none;">← Voltar para Aprovações</a>
        <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin-top:8px;line-height:1.3;">
            {{ $approval->isCritical() ? '🔴 ' : '' }}{{ $approval->title }}
        </h1>
        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
            <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approval->status_label }}</span>
            <span style="background:{{ $approval->sensitive_badge_color }};color:#fff;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approval->sensitive_level_label }}</span>
            <span style="background:#f1f5f9;color:#64748b;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $approval->approval_type_label }}</span>
            @if($employee)
            <span style="background:#ede9fe;color:#5b21b6;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">🤖 {{ $employee->name }}</span>
            @endif
            @if($task)
            <span style="background:#e0f2fe;color:#0369a1;font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">⚙ {{ $task->title }}</span>
            @endif
        </div>
    </div>
    <div style="font-size:.78rem;color:#94a3b8;text-align:right;">
        <div>Criado em {{ $approval->created_at->format('d/m/Y H:i') }}</div>
        @if($approval->due_at)
        <div style="color:{{ $approval->isOverdue() ? '#dc2626' : '#64748b' }};">
            Vencimento: {{ $approval->due_at->format('d/m/Y H:i') }}
            @if($approval->isOverdue()) <strong>(vencida)</strong>@endif
        </div>
        @endif
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#166534;font-size:.875rem;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#b91c1c;font-size:.875rem;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif
@if($imageAlert)
<div style="background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#92400e;font-size:.875rem;">
    ⚠ Esta tarefa exige imagem, mas a imagem ainda não foi gerada. Você pode aprovar mesmo assim, mas revise se necessário.
</div>
@endif

{{-- Decision bar (sticky) --}}
@if($approval->isPending())
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.04);">
    <h2 style="font-size:.88rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Tomar Decisão</h2>
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <textarea id="notesField" rows="2" style="width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;" placeholder="Observações sobre sua decisão (opcional)..."></textarea>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.approvals.approve', $approval->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="notes" id="notesApprove">
                <button type="submit" onclick="document.getElementById('notesApprove').value=document.getElementById('notesField').value" style="background:#16a34a;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">✓ Aprovar</button>
            </form>
            <form method="POST" action="{{ route('admin.approvals.reject', $approval->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="notes" id="notesReject">
                <button type="submit" onclick="document.getElementById('notesReject').value=document.getElementById('notesField').value" style="background:#dc2626;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">✕ Rejeitar</button>
            </form>
            <form method="POST" action="{{ route('admin.approvals.request-changes', $approval->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="notes" id="notesChanges">
                <button type="submit" onclick="document.getElementById('notesChanges').value=document.getElementById('notesField').value" style="background:#d97706;color:#fff;font-size:.82rem;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">↩ Alteração</button>
            </form>
            <form method="POST" action="{{ route('admin.approvals.cancel', $approval->id) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="notes" id="notesCancel">
                <button type="submit" onclick="document.getElementById('notesCancel').value=document.getElementById('notesField').value" style="background:#f1f5f9;color:#64748b;font-size:.82rem;font-weight:600;padding:10px 18px;border-radius:8px;border:1px solid #e2e8f0;cursor:pointer;">Cancelar</button>
            </form>
        </div>
    </div>
</div>
@else
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;gap:12px;align-items:center;">
    <span style="font-size:.875rem;color:#64748b;">Status da decisão:</span>
    <span style="background:{{ $approval->status_badge_color }};color:#fff;font-size:.8rem;font-weight:600;padding:4px 14px;border-radius:12px;">{{ $approval->status_label }}</span>
    @if($approval->isApproved())
    <span style="font-size:.8rem;color:#16a34a;">por {{ $approval->approvedBy?->name ?? '—' }} em {{ $approval->approved_at?->format('d/m/Y H:i') }}</span>
    @elseif($approval->isRejected())
    <span style="font-size:.8rem;color:#dc2626;">por {{ $approval->rejectedBy?->name ?? '—' }} em {{ $approval->rejected_at?->format('d/m/Y H:i') }}</span>
    @endif
</div>
@endif

{{-- Tabs --}}
<div id="approvalTabs" style="margin-bottom:20px;">

    {{-- Tab Nav --}}
    <div style="display:flex;gap:4px;border-bottom:2px solid #e2e8f0;margin-bottom:20px;flex-wrap:wrap;">
        @php
        $tabs = [
            ['id'=>'content',  'label'=>'Conteúdo Gerado'],
            ['id'=>'visual',   'label'=>'Imagem / Criativo'],
            ['id'=>'context',  'label'=>'Contexto Usado'],
            ['id'=>'execution','label'=>'Execução e Logs'],
            ['id'=>'debug',    'label'=>'Payload Técnico'],
        ];
        @endphp
        @foreach($tabs as $t)
        <button onclick="showTab('{{ $t['id'] }}')" id="tab-btn-{{ $t['id'] }}"
            style="background:none;border:none;border-bottom:2px solid transparent;padding:10px 16px;font-size:.82rem;font-weight:600;cursor:pointer;border-radius:6px 6px 0 0;transition:color .15s;color:#64748b;margin-bottom:-2px;">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ===== TAB: CONTEÚDO GERADO ===== --}}
    <div id="tab-content" class="tab-panel">

        @if(!empty($content['missing']) && $content['missing'])
        <div style="background:#fefce8;border:1px solid #fde047;border-radius:12px;padding:20px;color:#78350f;font-size:.875rem;">
            ⚠ Conteúdo não encontrado no pacote gerado. Verifique o payload técnico na aba "Payload Técnico".
        </div>
        @elseif($isBlog)

        {{-- Blog Post Content --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Título</div>
                <div style="font-size:1rem;font-weight:700;color:#0f172a;line-height:1.4;">{{ $content['title'] ?? '—' }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Slug</div>
                <div style="font-size:.875rem;color:#334155;font-family:monospace;">{{ $content['slug'] ?? '—' }}</div>
            </div>
        </div>

        @if(!empty($content['excerpt']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Resumo / Excerpt</div>
            <div style="font-size:.875rem;color:#334155;line-height:1.6;">{{ $content['excerpt'] }}</div>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">SEO Title</div>
                <div style="font-size:.875rem;color:#334155;">{{ $content['seo_title'] ?? '—' }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Meta Description</div>
                <div style="font-size:.875rem;color:#334155;">{{ $content['seo_description'] ?? '—' }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Palavra-chave Principal</div>
                <div style="font-size:.875rem;color:#4f46e5;font-weight:600;">{{ $content['focus_keyword'] ?? '—' }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Palavras-chave Secundárias</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    @forelse((array)($content['secondary_keywords'] ?? []) as $kw)
                    <span style="background:#f1f5f9;color:#475569;font-size:.72rem;padding:3px 8px;border-radius:8px;">{{ $kw }}</span>
                    @empty
                    <span style="color:#94a3b8;font-size:.8rem;">—</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tags and Categories --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            @if(!empty($content['tags']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Tags</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    @foreach((array)$content['tags'] as $tag)
                    <span style="background:#dcfce7;color:#166534;font-size:.72rem;padding:3px 8px;border-radius:8px;">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            @if(!empty($content['categories']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Categorias</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    @foreach((array)$content['categories'] as $cat)
                    <span style="background:#dbeafe;color:#1d4ed8;font-size:.72rem;padding:3px 8px;border-radius:8px;">{{ $cat }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if(!empty($content['image_alt']) || !empty($content['image_caption']))
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            @if(!empty($content['image_alt']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Alt Text da Imagem</div>
                <div style="font-size:.875rem;color:#334155;">{{ $content['image_alt'] }}</div>
            </div>
            @endif
            @if(!empty($content['image_caption']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Legenda da Imagem</div>
                <div style="font-size:.875rem;color:#334155;">{{ $content['image_caption'] }}</div>
            </div>
            @endif
        </div>
        @endif

        @if(!empty($content['cta_final']))
        <div style="background:#ede9fe;border:1px solid #c4b5fd;border-radius:12px;padding:18px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#5b21b6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">CTA Final</div>
            <div style="font-size:.875rem;color:#3b0764;line-height:1.6;">{{ $content['cta_final'] }}</div>
        </div>
        @endif

        {{-- Full article --}}
        @if(!empty($content['content_html']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Artigo Completo</div>
            <div class="prose-content" style="font-size:.9rem;color:#1e293b;line-height:1.8;max-height:600px;overflow-y:auto;padding-right:8px;">
                {!! nl2br(e($content['content_html'])) !!}
            </div>
            @if(strlen($content['content_html']) > 3000)
            <div style="margin-top:12px;text-align:center;">
                <button onclick="this.closest('.prose-content') ? document.querySelector('.prose-content').style.maxHeight='none' : null;this.style.display='none';" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;font-size:.78rem;padding:6px 16px;border-radius:8px;cursor:pointer;">
                    Expandir artigo completo
                </button>
            </div>
            @endif
        </div>
        @elseif(!empty($content['content_markdown']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Artigo Completo (Markdown)</div>
            <pre style="font-size:.85rem;color:#334155;white-space:pre-wrap;line-height:1.7;max-height:600px;overflow-y:auto;">{{ $content['content_markdown'] }}</pre>
        </div>
        @endif

        {{-- Edit link --}}
        @if($entity && $entityType === 'BlogPost')
        <div style="margin-bottom:16px;">
            <a href="{{ route('admin.blog.posts.edit', $entity->id) }}" style="background:#f8fafc;border:1px solid #e2e8f0;color:#4f46e5;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;display:inline-block;">
                ✏ Editar artigo antes de aprovar
            </a>
        </div>
        @endif

        @elseif($isSocial)

        {{-- Social Post Content --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Título</div>
                <div style="font-size:.95rem;font-weight:700;color:#0f172a;">{{ $content['title'] ?? '—' }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Tipo / Formato</div>
                <div style="font-size:.875rem;color:#334155;">{{ ucfirst($content['type'] ?? '—') }}</div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Legenda</div>
            <div style="font-size:.9rem;color:#1e293b;line-height:1.7;white-space:pre-line;">{{ $content['caption'] ?? '—' }}</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            @if(!empty($content['hashtags']))
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Hashtags</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach((array)$content['hashtags'] as $ht)
                    <span style="background:#e0f2fe;color:#0369a1;font-size:.75rem;padding:3px 8px;border-radius:8px;">{{ str_starts_with($ht,'#') ? $ht : '#'.$ht }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            @if(!empty($content['cta']))
            <div style="background:#ede9fe;border:1px solid #c4b5fd;border-radius:12px;padding:18px;">
                <div style="font-size:.7rem;color:#5b21b6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">CTA</div>
                <div style="font-size:.875rem;color:#3b0764;">{{ $content['cta'] }}</div>
            </div>
            @endif
        </div>

        @if(!empty($content['creative_brief']))
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:18px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#166534;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Brief Criativo</div>
            <div style="font-size:.875rem;color:#166534;line-height:1.6;">{{ $content['creative_brief'] }}</div>
        </div>
        @endif

        {{-- Carousel slides --}}
        @if($isCarousel && !empty($content['slides']))
        <div style="margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Slides do Carrossel</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($content['slides'] as $idx => $slide)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:8px;">Slide {{ $idx + 1 }}</div>
                    @if(!empty($slide['headline']))
                    <div style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:6px;">{{ $slide['headline'] }}</div>
                    @endif
                    @if(!empty($slide['body']))
                    <div style="font-size:.875rem;color:#475569;line-height:1.6;">{{ $slide['body'] }}</div>
                    @endif
                    @if(!empty($slide['visual_direction']))
                    <div style="font-size:.78rem;color:#64748b;margin-top:8px;font-style:italic;">Visual: {{ $slide['visual_direction'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Edit link for social --}}
        @if($entity && $entityType === 'SocialPost')
        <a href="{{ route('admin.social.posts.edit', $entity->id) }}" style="background:#f8fafc;border:1px solid #e2e8f0;color:#4f46e5;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;display:inline-block;margin-bottom:16px;">
            ✏ Editar post antes de aprovar
        </a>
        @endif

        @else
        {{-- Generic / unknown type --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Conteúdo</div>
            @if(!empty($approval->description))
            <div style="font-size:.875rem;color:#334155;line-height:1.6;">{{ $approval->description }}</div>
            @else
            <div style="font-size:.875rem;color:#94a3b8;">Tipo de conteúdo não identificado. Veja o Payload Técnico para detalhes.</div>
            @endif
        </div>
        @endif

    </div>{{-- end tab content --}}

    {{-- ===== TAB: IMAGEM / CRIATIVO ===== --}}
    <div id="tab-visual" class="tab-panel" style="display:none;">
        @if($hasImage && !empty($visual['featured_url']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;text-align:center;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Imagem Gerada</div>
            <img src="{{ $visual['featured_url'] }}" alt="Imagem gerada" style="max-width:100%;max-height:500px;border-radius:10px;object-fit:contain;">
            <div style="margin-top:10px;">
                <a href="{{ $visual['featured_url'] }}" target="_blank" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;">Abrir imagem</a>
            </div>
        </div>
        @endif

        {{-- Assets list --}}
        @if(!empty($visual['assets']))
        <div style="margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">Assets Gerados</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($visual['assets'] as $asset)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                        <div style="flex:1;">
                            <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                <span style="background:#f1f5f9;color:#475569;font-size:.72rem;padding:3px 8px;border-radius:6px;">{{ $asset['type'] ?? '—' }}</span>
                                @if($asset['slide_order'])
                                <span style="background:#e0f2fe;color:#0369a1;font-size:.72rem;padding:3px 8px;border-radius:6px;">Slide {{ $asset['slide_order'] }}</span>
                                @endif
                                <span style="background:{{ $asset['status']==='generated' ? '#f0fdf4' : ($asset['status']==='failed' ? '#fef2f2' : '#fefce8') }};color:{{ $asset['status']==='generated' ? '#166534' : ($asset['status']==='failed' ? '#b91c1c' : '#92400e') }};font-size:.72rem;padding:3px 8px;border-radius:6px;">{{ $asset['status_label'] }}</span>
                            </div>
                            @if($asset['prompt'])
                            <div style="font-size:.8rem;color:#64748b;line-height:1.5;">Prompt: {{ \Str::limit($asset['prompt'], 150) }}</div>
                            @endif
                            @if($asset['error'])
                            <div style="font-size:.78rem;color:#dc2626;margin-top:4px;">Erro: {{ $asset['error'] }}</div>
                            @endif
                        </div>
                        @if($asset['public_url'])
                        <div style="flex-shrink:0;">
                            <a href="{{ $asset['public_url'] }}" target="_blank" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#4f46e5;font-size:.75rem;padding:6px 12px;border-radius:8px;text-decoration:none;">Abrir</a>
                        </div>
                        @endif
                    </div>
                    @if($asset['is_generated'] && $asset['public_url'])
                    <div style="margin-top:12px;">
                        <img src="{{ $asset['public_url'] }}" alt="asset" style="max-width:100%;max-height:300px;border-radius:8px;object-fit:contain;">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Visual prompt --}}
        @if(!empty($visual['image_prompt']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:16px;">
            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Prompt Visual Usado</div>
            <div style="font-size:.875rem;color:#334155;line-height:1.6;">{{ $visual['image_prompt'] }}</div>
        </div>
        @endif

        {{-- No image --}}
        @if(!$hasImage && empty($visual['assets']))
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:24px;text-align:center;color:#94a3b8;">
            @if($visual['visual_status'] === 'pending_configuration')
            <div style="font-size:.9rem;">Imagem não configurada nesta tarefa</div>
            @elseif(in_array($visual['visual_status'] ?? '', ['failed','error']))
            <div style="font-size:.9rem;color:#dc2626;">Geração de imagem falhou</div>
            @else
            <div style="font-size:.9rem;">Imagem ainda não gerada</div>
            @endif
            @if(!empty($visual['image_prompt']))
            <div style="font-size:.8rem;margin-top:8px;color:#64748b;">Prompt configurado: {{ \Str::limit($visual['image_prompt'], 100) }}</div>
            @endif
        </div>
        @endif

    </div>{{-- end tab visual --}}

    {{-- ===== TAB: CONTEXTO USADO ===== --}}
    <div id="tab-context" class="tab-panel" style="display:none;">

        @if(!empty($ctxData['missing']))
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:20px;color:#b91c1c;font-size:.875rem;">
            Contexto de execução não encontrado para esta aprovação.
        </div>
        @else

        {{-- Brand Context --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Brand Context</div>
            @if($ctxData['brand_context_id'])
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">ID</div>
                    <div style="font-size:.82rem;color:#334155;">#{{ $ctxData['brand_context_id'] }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Versão</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $ctxData['brand_context_version'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Hash</div>
                    <div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">{{ \Str::limit($ctxData['brand_context_hash'] ?? '—', 16) }}</div>
                </div>
            </div>
            @if(!empty($ctxData['compact_brand_context']))
            <details style="margin-top:8px;">
                <summary style="font-size:.78rem;color:#4f46e5;cursor:pointer;">Ver contexto compacto usado</summary>
                <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;font-size:.78rem;color:#475569;white-space:pre-wrap;margin-top:8px;max-height:300px;overflow-y:auto;">{{ $ctxData['compact_brand_context'] }}</pre>
            </details>
            @endif
            @else
            <div style="font-size:.875rem;color:#94a3b8;">Brand Context não encontrado no snapshot</div>
            @endif
        </div>

        {{-- Task Context --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Tarefa Operacional</div>
            @if($task || $ctxData['task_title'])
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Título</div>
                    <div style="font-size:.875rem;color:#1e293b;font-weight:600;">{{ $task?->title ?? $ctxData['task_title'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Tipo</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $task?->task_type ?? $ctxData['task_type'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Canal</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $task?->content_channel ?? $ctxData['task_channel'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Formato</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $task?->content_format ?? $ctxData['task_format'] ?? '—' }}</div>
                </div>
            </div>
            @if(!empty($ctxData['compact_task_context']))
            <details style="margin-top:8px;">
                <summary style="font-size:.78rem;color:#4f46e5;cursor:pointer;">Ver contexto compacto da tarefa</summary>
                <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;font-size:.78rem;color:#475569;white-space:pre-wrap;margin-top:8px;max-height:300px;overflow-y:auto;">{{ $ctxData['compact_task_context'] }}</pre>
            </details>
            @endif
            @else
            <div style="font-size:.875rem;color:#94a3b8;">Tarefa operacional não identificada</div>
            @endif
        </div>

        {{-- Employee --}}
        @if($employee)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Funcionário IA</div>
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;background:#ede9fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;">🤖</div>
                <div>
                    <div style="font-size:.9rem;font-weight:600;color:#1e293b;">{{ $employee->name }}</div>
                    <div style="font-size:.78rem;color:#64748b;">{{ $employee->title ?? $employee->role_key ?? '—' }}</div>
                </div>
                <span style="background:{{ $employee->status === 'active' ? '#f0fdf4' : '#f8fafc' }};color:{{ $employee->status === 'active' ? '#166534' : '#94a3b8' }};font-size:.72rem;font-weight:600;padding:3px 10px;border-radius:8px;margin-left:auto;">{{ ucfirst($employee->status ?? '—') }}</span>
            </div>
        </div>
        @endif

        {{-- Memories --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Memórias Relevantes Usadas</div>
            @if(!empty($ctxData['memories']))
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($ctxData['memories'] as $mem)
                <div style="background:#f8fafc;border-radius:10px;padding:14px;">
                    <div style="display:flex;gap:8px;margin-bottom:6px;">
                        <span style="background:#ede9fe;color:#5b21b6;font-size:.7rem;padding:2px 8px;border-radius:6px;">{{ $mem['type'] }}</span>
                        <span style="font-size:.8rem;font-weight:600;color:#1e293b;">{{ $mem['title'] }}</span>
                    </div>
                    @if($mem['content'])
                    <div style="font-size:.8rem;color:#64748b;line-height:1.5;">{{ $mem['content'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div style="font-size:.875rem;color:#94a3b8;">Nenhuma memória relevante foi usada nesta execução</div>
            @endif
        </div>

        {{-- Sources --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Pesquisa / Fontes Externas</div>
            @if($sources['has_sources'])
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($sources['items'] as $src)
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px;">
                    @if(is_array($src))
                        @if(!empty($src['title'])) <div style="font-size:.82rem;font-weight:600;color:#0369a1;margin-bottom:4px;">{{ $src['title'] }}</div>@endif
                        @if(!empty($src['url'])) <a href="{{ $src['url'] }}" target="_blank" style="font-size:.75rem;color:#0284c7;">{{ $src['url'] }}</a>@endif
                        @if(!empty($src['summary'])) <div style="font-size:.78rem;color:#334155;margin-top:4px;">{{ $src['summary'] }}</div>@endif
                    @else
                        <div style="font-size:.82rem;color:#334155;">{{ $src }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div style="font-size:.875rem;color:#94a3b8;">Pesquisa externa não foi usada nesta execução</div>
            @endif
        </div>

        {{-- Prompt preview --}}
        @if(!empty($ctxData['prompt_preview']))
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Prompt Preview (sanitizado)</div>
            <details>
                <summary style="font-size:.78rem;color:#4f46e5;cursor:pointer;">Expandir prompt preview</summary>
                <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;font-size:.75rem;color:#475569;white-space:pre-wrap;margin-top:8px;max-height:400px;overflow-y:auto;">{{ $ctxData['prompt_preview'] }}</pre>
            </details>
        </div>
        @endif

        @endif{{-- end !missing --}}

    </div>{{-- end tab context --}}

    {{-- ===== TAB: EXECUÇÃO E LOGS ===== --}}
    <div id="tab-execution" class="tab-panel" style="display:none;">

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Execução e Custo</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Run ID</div>
                    <div style="font-size:.875rem;color:#334155;">#{{ $usage['run_id'] ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Status</div>
                    @if($usage['run_id'])
                    <span style="background:#f1f5f9;color:#475569;font-size:.75rem;font-weight:600;padding:3px 10px;border-radius:8px;">{{ $usage['status_label'] ?? $usage['status'] ?? '—' }}</span>
                    @else
                    <div style="font-size:.875rem;color:#94a3b8;">não registrado</div>
                    @endif
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Provider</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $usage['provider'] ?? 'não registrado' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Modelo</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $usage['model'] ?? 'não registrado' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Tokens entrada</div>
                    <div style="font-size:.875rem;color:#334155;">{{ number_format($usage['input_tokens'] ?? 0) }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Tokens saída</div>
                    <div style="font-size:.875rem;color:#334155;">{{ number_format($usage['output_tokens'] ?? 0) }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Custo estimado</div>
                    <div style="font-size:.875rem;color:#334155;">
                        {{ $usage['estimated_cost'] ? '$'.number_format($usage['estimated_cost'], 4) : 'não registrado' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Duração</div>
                    <div style="font-size:.875rem;color:#334155;">{{ $usage['duration'] ?? 'não registrado' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Iniciado em</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $usage['started_at']?->format('d/m/Y H:i:s') ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Finalizado em</div>
                    <div style="font-size:.82rem;color:#334155;">{{ $usage['finished_at']?->format('d/m/Y H:i:s') ?? '—' }}</div>
                </div>
            </div>
            @if($usage['error_message'])
            <div style="margin-top:16px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px;">
                <div style="font-size:.72rem;color:#b91c1c;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Erro</div>
                <div style="font-size:.82rem;color:#dc2626;">{{ $usage['error_message'] }}</div>
            </div>
            @endif
        </div>

        {{-- Recent activity logs --}}
        @php
        $activityLogs = \App\Models\ActivityLog::where(function($q) use ($approval, $run) {
            $q->where(function($q2) use ($approval) {
                $q2->where('module', 'approvals')
                   ->where(function($q3) use ($approval) {
                       $q3->whereJsonContains('metadata->approval_request_id', $approval->id)
                          ->orWhere('description', 'like', '%#'.$approval->id.'%');
                   });
            });
            if ($run) {
                $q->orWhere(function($q2) use ($run) {
                    $q2->whereJsonContains('metadata->task_run_id', $run->id)
                       ->orWhereJsonContains('metadata->agent_task_run_id', $run->id);
                });
            }
        })->orderByDesc('created_at')->limit(20)->get();
        @endphp
        @if($activityLogs->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Logs de Atividade</div>
            <div style="border:1px solid #f1f5f9;border-radius:8px;overflow:hidden;">
                <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Ação</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Módulo</th>
                            <th style="padding:8px 12px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activityLogs as $log)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px 12px;color:#334155;">{{ \Str::limit($log->description ?? $log->action, 80) }}</td>
                            <td style="padding:8px 12px;color:#64748b;">{{ $log->module ?? '—' }}</td>
                            <td style="padding:8px 12px;color:#94a3b8;">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- History + comments --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
                <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Histórico de Ações</div>
                @forelse($approval->actions as $action)
                <div style="display:flex;gap:10px;margin-bottom:12px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $action->action_color }};margin-top:5px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:.8rem;font-weight:600;color:#334155;">{{ $action->action_label }}</div>
                        <div style="font-size:.75rem;color:#64748b;">{{ $action->user?->name ?? 'Sistema' }}</div>
                        @if($action->notes)
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;font-style:italic;">"{{ \Str::limit($action->notes, 80) }}"</div>
                        @endif
                        <div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">{{ $action->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @empty
                <p style="font-size:.8rem;color:#94a3b8;">Sem histórico.</p>
                @endforelse
            </div>

            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
                <div style="font-size:.8rem;font-weight:700;color:#1e293b;margin-bottom:12px;">Notificações por E-mail</div>
                @php
                    $emailStatus = $approval->notification_status ?? 'not_sent';
                    $scColors = ['sent'=>['#f0fdf4','#86efac','#166534','Enviado'],'failed'=>['#fef2f2','#fca5a5','#b91c1c','Falha'],'not_sent'=>['#f8fafc','#e2e8f0','#64748b','Não enviado'],'disabled'=>['#f8fafc','#e2e8f0','#94a3b8','Desabilitado']];
                    $sc = $scColors[$emailStatus] ?? $scColors['not_sent'];
                @endphp
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <span style="background:{{ $sc[0] }};border:1px solid {{ $sc[1] }};color:{{ $sc[2] }};font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:12px;">{{ $sc[3] }}</span>
                </div>
                @if($approval->isPending())
                <form method="POST" action="{{ route('admin.approvals.resend-email', $approval) }}" style="margin-bottom:12px;">
                    @csrf
                    <button type="submit" style="background:#4f46e5;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:.75rem;font-weight:600;cursor:pointer;">Reenviar e-mail</button>
                </form>
                @endif
                @php $emailLogs = $approval->emailNotifications()->limit(5)->get(); @endphp
                @forelse($emailLogs as $log)
                <div style="font-size:.75rem;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#334155;">{{ $log->email }}</span>
                    <span style="color:{{ $log->status==='sent' ? '#16a34a' : '#dc2626' }};margin-left:8px;font-weight:600;">{{ ucfirst($log->status) }}</span>
                    <span style="color:#94a3b8;margin-left:8px;">{{ ($log->sent_at ?? $log->created_at)?->format('d/m H:i') }}</span>
                </div>
                @empty
                <p style="font-size:.78rem;color:#94a3b8;">Nenhum e-mail registrado.</p>
                @endforelse
            </div>
        </div>

    </div>{{-- end tab execution --}}

    {{-- ===== TAB: PAYLOAD TÉCNICO ===== --}}
    <div id="tab-debug" class="tab-panel" style="display:none;">
        <div style="background:#fef9c3;border:1px solid #fde047;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:.8rem;color:#78350f;">
            Esta aba contém dados técnicos/raw para diagnóstico. Não representa o conteúdo operacional da aprovação.
        </div>

        @foreach([
            ['label'=>'Payload da Aprovação', 'data'=> $debug['approval_payload']],
            ['label'=>'Content Payload (Package)', 'data'=> $debug['content_payload']],
            ['label'=>'Visual Payload (Package)', 'data'=> $debug['visual_payload']],
            ['label'=>'Sources Payload (Package)', 'data'=> $debug['sources_payload']],
            ['label'=>'Context Snapshot', 'data'=> $debug['context_snapshot']],
        ] as $dbg)
        @if($dbg['data'])
        <details style="margin-bottom:12px;">
            <summary style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;cursor:pointer;font-size:.82rem;font-weight:600;color:#334155;list-style:none;display:flex;justify-content:space-between;">
                {{ $dbg['label'] }} <span style="color:#94a3b8;">▼</span>
            </summary>
            <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0 0 10px 10px;padding:16px;font-size:.72rem;color:#475569;overflow-x:auto;white-space:pre-wrap;max-height:500px;overflow-y:auto;margin:0;">{{ json_encode($dbg['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
        @endif
        @endforeach

    </div>{{-- end tab debug --}}

</div>{{-- end tabs --}}

<script>
function showTab(name) {
    document.querySelectorAll('.tab-panel').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('[id^="tab-btn-"]').forEach(function(btn) {
        btn.style.borderBottomColor = 'transparent';
        btn.style.color = '#64748b';
    });
    var panel = document.getElementById('tab-' + name);
    if (panel) panel.style.display = 'block';
    var btn = document.getElementById('tab-btn-' + name);
    if (btn) { btn.style.borderBottomColor = '#4f46e5'; btn.style.color = '#4f46e5'; }
}
document.addEventListener('DOMContentLoaded', function() { showTab('content'); });
</script>

{{-- Comments section --}}
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;">
    <h2 style="font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:16px;">Comentários ({{ $approval->comments->count() }})</h2>

    @forelse($approval->comments as $comment)
    <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:.8rem;font-weight:600;color:#1e293b;">{{ $comment->user?->name ?? 'Sistema' }}</span>
            <span style="font-size:.72rem;color:#94a3b8;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <p style="font-size:.875rem;color:#475569;margin:0;line-height:1.5;">{{ $comment->comment }}</p>
    </div>
    @empty
    <p style="font-size:.875rem;color:#94a3b8;">Nenhum comentário ainda.</p>
    @endforelse

    <form method="POST" action="{{ route('admin.approvals.comments', $approval->id) }}" style="margin-top:16px;">
        @csrf
        <textarea name="comment" rows="3" required style="width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#334155;font-size:.875rem;resize:vertical;margin-bottom:10px;" placeholder="Adicione um comentário..."></textarea>
        <button type="submit" style="background:#4f46e5;color:#fff;font-size:.8rem;font-weight:600;padding:8px 18px;border-radius:8px;border:none;cursor:pointer;">Comentar</button>
    </form>
</div>

</x-layouts.app>
